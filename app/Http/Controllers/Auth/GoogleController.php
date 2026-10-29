<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Ingreso con Google. Si el correo ya tiene cuenta, entra con ella (también jurados y admins).
 * Si es nuevo, antes de crear la cuenta pide la autorización de datos y el perfil (comunidad o inversionista).
 */
class GoogleController extends Controller
{
    public static function enabled(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    public function redirect(Request $request): RedirectResponse
    {
        abort_unless(self::enabled(), 404);
        $request->session()->put('google_role', $request->query('perfil') === 'inversionista' ? 'inversionista' : 'comunidad');

        return Socialite::driver('google')->with(['prompt' => 'select_account'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless(self::enabled(), 404);

        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            // Queda en storage/logs/laravel.log para diagnosticar (estado de sesión perdido, secreto inválido, etc.)
            Log::warning('Ingreso con Google falló: '.$e::class.' — '.Str::limit($e->getMessage(), 500));

            return redirect()->route('login')->withErrors(['email' => 'No se pudo ingresar con Google. Inténtalo de nuevo.']);
        }

        $email = Str::lower((string) $google->getEmail());
        if (! $email || ! ($google->getRaw()['email_verified'] ?? false)) {
            return redirect()->route('login')->withErrors(['email' => 'Tu cuenta de Google no tiene un correo verificado.']);
        }

        $user = User::where('google_id', $google->getId())->first() ?? User::where('email', $email)->first();
        if ($user) {
            $user->forceFill([
                'google_id' => $user->google_id ?? $google->getId(),
                'email_verified_at' => $user->email_verified_at ?? now(), // Google ya confirmó que el correo es suyo
            ])->save();

            return $this->login($request, $user);
        }

        $request->session()->put('google_signup', ['id' => $google->getId(), 'email' => $email, 'name' => $google->getName() ?: Str::before($email, '@')]);

        return redirect()->route('google.complete');
    }

    /** Paso final para cuentas nuevas: perfil y autorización de tratamiento de datos. */
    public function complete(Request $request)
    {
        $data = $request->session()->get('google_signup');
        if (! $data) {
            return redirect()->route('register');
        }

        return view('auth.google-complete', ['google' => $data, 'role' => $request->session()->get('google_role', 'comunidad')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->session()->get('google_signup');
        if (! $data) {
            return redirect()->route('register');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(['comunidad', 'inversionista'])],
            'phone' => ['nullable', 'string', 'max:30', 'required_if:role,inversionista'],
            'organization' => ['nullable', 'string', 'max:255'],
            'academic_program' => ['nullable', 'string', 'max:255'],
            'terms' => ['accepted'],
        ], [
            'phone.required_if' => 'Como inversionista/aliado, déjanos un teléfono de contacto.',
            'terms.accepted' => 'Debes aceptar el tratamiento de datos personales.',
        ]);

        // Si mientras tanto se creó una cuenta con ese correo, se usa esa
        $user = User::where('email', $data['email'])->first() ?? User::create([
            'name' => $request->name,
            'email' => $data['email'],
            'role' => $request->role,
            'phone' => $request->phone,
            'organization' => $request->organization,
            'academic_program' => $request->academic_program,
            'password' => Hash::make(Str::password(32)), // puede crear una propia con «¿La olvidaste?»
        ]);
        $user->forceFill(['google_id' => $user->google_id ?? $data['id'], 'email_verified_at' => $user->email_verified_at ?? now()])->save();
        $request->session()->forget(['google_signup', 'google_role']);

        return $this->login($request, $user);
    }

    private function login(Request $request, User $user): RedirectResponse
    {
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(match (true) {
            $user->isAdmin() => route('admin.dashboard'),
            $user->isJury() => route('jury.index'),
            default => RouteServiceProvider::HOME,
        });
    }
}
