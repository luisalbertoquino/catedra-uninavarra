<x-layouts.admin title="Usuarios">
    <x-slot:actions>
        <a href="{{ route('admin.users.export', request()->query()) }}" class="a-btn-light"><x-icon name="file-arrow-down" /> Exportar CSV</a>
    </x-slot:actions>

    <form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row">
        <select name="rol" class="a-input sm:!w-56" onchange="this.form.submit()">
            <option value="">Todos los roles</option>
            @foreach (\App\Models\User::ROLES as $k => $l)<option value="{{ $k }}" @selected(request('rol') === $k)>{{ $l }}</option>@endforeach
        </select>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Nombre, correo u organización…" class="a-input sm:!w-80">
        <button class="a-btn-light">Buscar</button>
    </form>

    <div class="a-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead><tr><th>Usuario</th><th>Contacto</th><th class="text-center">Votos</th><th class="text-center">Coment.</th><th class="text-center">Apoyos</th><th>Registro</th><th>Rol</th><th></th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $u)
                        <tr class="hover:bg-slate-50">
                            <td>
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$u->name" size="sm" />
                                    <div>
                                        <div class="font-semibold text-ink-900">{{ $u->name }}</div>
                                        <div class="text-xs text-slate-600">{{ $u->email }} @if ($u->email_verified_at)<span title="Verificado"><x-icon name="circle-check" /></span>@else<span class="text-slate-500" title="Sin verificar">·</span>@endif</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-xs text-slate-600">{{ $u->phone }}<br>{{ $u->organization ?? $u->academic_program }}</td>
                            <td class="text-center">{{ $u->votes_count }}</td>
                            <td class="text-center">{{ $u->comments_count }}</td>
                            <td class="text-center">{{ $u->pledges_count }}</td>
                            <td class="whitespace-nowrap text-slate-600">{{ $u->created_at->translatedFormat('j M Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.update', $u) }}">@csrf @method('PATCH')
                                    <select name="role" class="a-input !w-auto !py-1.5 pr-8 text-xs" onchange="this.form.submit()">
                                        @foreach (\App\Models\User::ROLES as $k => $l)<option value="{{ $k }}" @selected($u->role === $k)>{{ $l }}</option>@endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($u->isAdmin() || $u->isJury())
                                        <form method="POST" action="{{ route('admin.users.invite', $u) }}" onsubmit="return confirm('¿Enviar a {{ $u->email }} el correo de bienvenida con el enlace para crear su contraseña?')">@csrf
                                            <button class="a-btn-light !py-1.5 text-xs" title="Enviar correo de bienvenida"><x-icon name="envelope" style="regular" /> Invitar</button>
                                        </form>
                                    @endif
                                    @if (auth()->user()->isSuperAdmin() && ! $u->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm(@js("¿Borrar la cuenta de {$u->email}? También se borran sus {$u->votes_count} voto(s), {$u->comments_count} comentario(s) y {$u->pledges_count} apoyo(s). No se puede deshacer."))">
                                            @csrf @method('DELETE')
                                            <button class="a-btn !py-1.5 text-rose-600 hover:bg-rose-50" title="Borrar cuenta"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
</x-layouts.admin>
