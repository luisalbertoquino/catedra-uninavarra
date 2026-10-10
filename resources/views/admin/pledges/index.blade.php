<x-layouts.admin title="Intenciones de apoyo">
    <x-slot:actions>
        <a href="{{ route('admin.pledges.export', request()->query()) }}" class="a-btn-light">⬇ Exportar CSV</a>
    </x-slot:actions>

    <div class="mb-6 flex gap-2 overflow-x-auto pb-1">
        <a href="{{ route('admin.pledges.index', request()->except('estado', 'page')) }}" @class(['whitespace-nowrap rounded-full px-4 py-1.5 text-sm font-medium', 'bg-ink-800 text-white' => ! request('estado'), 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => request('estado')])>Todas · {{ $counts->sum() }}</a>
        @foreach (\App\Models\SupportPledge::STATUSES as $k => $label)
            <a href="{{ route('admin.pledges.index', array_merge(request()->except('page'), ['estado' => $k])) }}" @class(['whitespace-nowrap rounded-full px-4 py-1.5 text-sm font-medium', 'bg-ink-800 text-white' => request('estado') === $k, 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => request('estado') !== $k])>{{ $label }} · {{ $counts[$k] ?? 0 }}</a>
        @endforeach
    </div>

    <form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row">
        <input type="hidden" name="estado" value="{{ request('estado') }}">
        <select name="tipo" class="a-input sm:!w-48" onchange="this.form.submit()">
            <option value="">Todos los tipos</option>
            @foreach (\App\Models\SupportPledge::TYPES as $k => $t)<option value="{{ $k }}" @selected(request('tipo') === $k)>{{ $t['icon'] }} {{ $t['label'] }}</option>@endforeach
        </select>
        <select name="edicion" class="a-input sm:!w-64" onchange="this.form.submit()">
            <option value="">Todas las ediciones</option>
            @foreach ($editions as $e)<option value="{{ $e->id }}" @selected(request('edicion') == $e->id)>{{ $e->name }}</option>@endforeach
        </select>
    </form>

    <div class="space-y-3">
        @forelse ($pledges as $p)
            <div x-data="{ edit: false }" class="a-card p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start">
                    <div class="flex flex-1 gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-slate-100 text-2xl">{{ \App\Models\SupportPledge::TYPES[$p->type]['icon'] ?? '•' }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-ink-900">{{ $p->user->name }}</span>
                                <span class="text-slate-400">→</span>
                                <a href="{{ route('ventures.show', $p->venture) }}" target="_blank" class="font-semibold text-ink-700 hover:underline">{{ $p->venture->name }}</a>
                                <x-admin.status :status="$p->status" />
                            </div>
                            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500">
                                <span>{{ $p->type_label }}@if ($p->amount) · <b class="text-slate-700">${{ number_format($p->amount, 0, ',', '.') }}</b>@endif</span>
                                <a href="mailto:{{ $p->user->email }}" class="hover:text-ink-700">✉ {{ $p->user->email }}</a>
                                @if ($p->user->phone)<a href="https://wa.me/{{ (strlen($d = preg_replace('/\D/', '', $p->user->phone)) === 10 ? '57' : '').$d }}" target="_blank" class="hover:text-emerald-600">📱 {{ $p->user->phone }}</a>@endif
                                @if ($p->user->organization)<span>🏢 {{ $p->user->organization }}</span>@endif
                                <span>{{ $p->created_at->translatedFormat('j M Y, g:i a') }}</span>
                            </div>
                            <p class="mt-3 rounded-xl bg-slate-50 p-3 text-sm text-slate-700">{{ $p->message }}</p>
                            @if ($p->admin_notes)<p class="mt-2 text-sm text-slate-500"><b>Notas:</b> {{ $p->admin_notes }}</p>@endif
                        </div>
                    </div>
                    <button type="button" @click="edit = !edit" class="a-btn-light shrink-0">Gestionar</button>
                </div>
                <form x-cloak x-show="edit" x-collapse method="POST" action="{{ route('admin.pledges.update', $p) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-4">
                    @csrf @method('PATCH')
                    <select name="status" class="a-input">
                        @foreach (\App\Models\SupportPledge::STATUSES as $k => $label)<option value="{{ $k }}" @selected($p->status === $k)>{{ $label }}</option>@endforeach
                    </select>
                    <input name="admin_notes" value="{{ $p->admin_notes }}" class="a-input sm:col-span-2" placeholder="Notas internas (seguimiento, acuerdos…)">
                    <button class="a-btn-primary">Guardar</button>
                </form>
            </div>
        @empty
            <div class="a-card p-12 text-center text-slate-400">No hay intenciones de apoyo con estos filtros.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $pledges->links() }}</div>
</x-layouts.admin>
