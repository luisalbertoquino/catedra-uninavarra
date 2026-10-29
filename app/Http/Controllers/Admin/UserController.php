<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\WelcomeInvitation;
use App\Support\Csv;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = $this->query($request)->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', Rule::in(array_keys(User::ROLES))]]);

        if ($user->is($request->user()) && $data['role'] !== 'admin') {
            return back()->with('error', 'No puedes quitarte a ti mismo el rol de administrador.');
        }
        $user->update($data);

        return back()->with('status', "Rol de {$user->name} actualizado.");
    }

    /** Envía el correo de bienvenida con el enlace para crear o cambiar la contraseña (jurados y administradores). */
    public function invite(User $user)
    {
        if (! $user->isAdmin() && ! $user->isJury()) {
            return back()->with('error', 'Las invitaciones son para jurados y administradores.');
        }
        if (! WelcomeInvitation::send($user)) {
            return back()->with('error', "No se pudo enviar el correo a {$user->email}. Revisa el correo del servidor e inténtalo de nuevo.");
        }

        return back()->with('status', "Invitación enviada a {$user->email}.");
    }

    /** Borra una cuenta con sus votos, comentarios, apoyos y evaluaciones. Solo el administrador principal. */
    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Solo el administrador principal puede borrar usuarios.');
        if ($user->is($request->user())) {
            return back()->with('error', 'No puedes borrar tu propia cuenta.');
        }
        $user->delete(); // votos, comentarios, apoyos y evaluaciones se borran en cascada

        return back()->with('status', "Cuenta de {$user->email} borrada.");
    }

    public function export(Request $request)
    {
        $rows = $this->query($request)->get()->map(fn ($u) => [
            $u->name, $u->email, $u->role_label, $u->phone, $u->organization, $u->academic_program,
            $u->email_verified_at ? 'Sí' : 'No', $u->votes_count, $u->comments_count, $u->pledges_count,
            $u->created_at->format('Y-m-d'),
        ]);

        return Csv::download('usuarios.csv',
            ['Nombre', 'Correo', 'Rol', 'Teléfono', 'Organización', 'Programa', 'Verificado', 'Votos', 'Comentarios', 'Apoyos', 'Registro'],
            $rows);
    }

    private function query(Request $request)
    {
        return User::withCount(['votes', 'comments', 'pledges'])
            ->when($request->filled('rol'), fn ($q) => $q->where('role', $request->rol))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->q.'%')
                ->orWhere('email', 'like', '%'.$request->q.'%')->orWhere('organization', 'like', '%'.$request->q.'%')))
            ->latest();
    }
}
