<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class JuryController extends Controller
{
    public function index()
    {
        $edition = Edition::where('is_active', true)->first();
        $total = $edition ? $edition->ventures()->published()->count() : 0;

        $juries = User::where('role', 'jurado')
            ->withCount(['evaluations' => fn ($q) => $q->where('edition_id', $edition?->id)])
            ->withAvg(['evaluations' => fn ($q) => $q->where('edition_id', $edition?->id)], 'total_score')
            ->orderByDesc('is_expert')->orderBy('name')->get();

        return view('admin.juries.index', compact('juries', 'edition', 'total'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'organization' => ['nullable', 'string', 'max:150'],
            'password' => ['nullable', Password::min(8)->letters()->numbers()],
            'is_expert' => ['boolean'],
        ]);

        $password = $data['password'] ?? Str::password(12, symbols: false);
        User::create([
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
            'organization' => $data['organization'] ?? null,
            'role' => 'jurado',
            'is_expert' => $request->boolean('is_expert'),
            'password' => Hash::make($password),
        ])->markEmailAsVerified();

        return back()->with('status', 'Jurado creado.')
            ->with('credentials', ['email' => Str::lower($data['email']), 'password' => $password]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->isJury(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user)],
            'organization' => ['nullable', 'string', 'max:150'],
            'password' => ['nullable', Password::min(8)->letters()->numbers()],
        ]);

        $user->fill([
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
            'organization' => $data['organization'] ?? null,
            'is_expert' => $request->boolean('is_expert'),
        ]);
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('status', "Jurado {$user->name} actualizado.");
    }

    public function destroy(User $user)
    {
        abort_unless($user->isJury(), 404);
        $user->delete(); // sus evaluaciones se eliminan en cascada

        return back()->with('status', 'Jurado eliminado junto con sus evaluaciones.');
    }
}
