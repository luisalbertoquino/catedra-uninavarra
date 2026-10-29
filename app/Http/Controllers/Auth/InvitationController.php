<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\WelcomeInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/** Enlace del correo de bienvenida: la persona invitada crea su contraseña y entra a su panel. */
class InvitationController extends Controller
{
    public function show(Request $request, User $user, string $hash)
    {
        $this->check($user, $hash);

        return view('auth.invitation', ['user' => $user]);
    }

    public function store(Request $request, User $user, string $hash)
    {
        $this->check($user, $hash);

        $request->validate(['password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()]]);
        $user->forceFill(['password' => Hash::make($request->password), 'email_verified_at' => $user->email_verified_at ?? now()])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($user->isAdmin() ? route('admin.dashboard') : route('jury.index'))
            ->with('status', '¡Listo! Tu contraseña quedó guardada.');
    }

    private function check(User $user, string $hash): void
    {
        abort_unless(($user->isAdmin() || $user->isJury()) && hash_equals(WelcomeInvitation::hash($user), $hash), 403,
            'Este enlace ya se usó o no es válido. Pide uno nuevo con «¿La olvidaste?» en el inicio de sesión.');
    }
}
