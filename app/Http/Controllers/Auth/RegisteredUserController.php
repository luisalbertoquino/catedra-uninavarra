<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use App\Rules\HumanVerification;
use App\Rules\NotDisposableEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.register', ['role' => $request->query('perfil') === 'inversionista' ? 'inversionista' : 'comunidad']);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, new NotDisposableEmail],
            'cf-turnstile-response' => [new HumanVerification],
            'role' => ['required', Rule::in(['comunidad', 'inversionista'])],
            'phone' => ['nullable', 'string', 'max:30', 'required_if:role,inversionista'],
            'organization' => ['nullable', 'string', 'max:255'],
            'academic_program' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ], [
            'phone.required_if' => 'Como inversionista/aliado, déjanos un teléfono de contacto.',
            'terms.accepted' => 'Debes aceptar el tratamiento de datos personales.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'organization' => $request->organization,
            'academic_program' => $request->academic_program,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
