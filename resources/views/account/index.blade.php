<x-layouts.site title="Mi actividad">
    <section class="container-x pb-10 pt-32 lg:pt-40">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
            <x-avatar :name="$user->name" size="lg" />
            <div class="flex-1">
                <h1 class="font-display text-3xl font-bold sm:text-4xl">Hola, {{ Str::before($user->name, ' ') }} 👋</h1>
                <p class="mt-1 text-white/55">{{ $user->role_label }} · {{ $user->email }}</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="btn-ghost self-start sm:self-auto">Editar perfil</a>
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-3">
            <div class="card-dark p-6">
                <div class="text-sm text-white/50">Votos disponibles</div>
                <div class="mt-2 font-display text-4xl font-bold text-lion-300">{{ $votesLeft }}<span class="text-xl text-white/30"> / {{ $edition?->max_votes_per_user ?? 0 }}</span></div>
                <div class="mt-1 text-xs text-white/45">{{ $edition?->name }}</div>
            </div>
            <div class="card-dark p-6">
                <div class="text-sm text-white/50">Opiniones publicadas</div>
                <div class="mt-2 font-display text-4xl font-bold">{{ $user->comments()->count() }}</div>
            </div>
            <div class="card-dark p-6">
                <div class="text-sm text-white/50">Intenciones de apoyo</div>
                <div class="mt-2 font-display text-4xl font-bold">{{ $pledges->count() }}</div>
            </div>
        </div>

        <div class="mt-12 grid gap-10 lg:grid-cols-2">
            <div>
                <h2 class="font-display text-2xl font-bold">Mis votos</h2>
                <div class="mt-4 space-y-2">
                    @forelse ($votes as $vote)
                        <a href="{{ route('ventures.show', $vote->venture) }}" class="glass flex items-center gap-4 rounded-2xl p-4 hover:border-lion-400/40">
                            <span class="text-xl text-rose-500">♥</span>
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-semibold">{{ $vote->venture->name }}</div>
                                <div class="text-xs text-white/45">{{ $vote->venture->category?->name }} · {{ $vote->created_at->diffForHumans() }}</div>
                            </div>
                            <span class="text-white/30">→</span>
                        </a>
                    @empty
                        <div class="card-dark p-8 text-center text-white/55">Aún no has votado. <a href="{{ route('ventures.index') }}" class="font-semibold text-lion-300">Explora los emprendimientos →</a></div>
                    @endforelse
                </div>
            </div>

            <div>
                <h2 class="font-display text-2xl font-bold">Mis intenciones de apoyo</h2>
                <div class="mt-4 space-y-2">
                    @forelse ($pledges as $p)
                        <div class="glass rounded-2xl p-4">
                            <div class="flex items-center justify-between gap-3">
                                <a href="{{ route('ventures.show', $p->venture) }}" class="truncate font-semibold hover:text-lion-300">{{ $p->venture->name }}</a>
                                <span @class(['chip !py-0.5 shrink-0', 'text-lion-200' => $p->status === 'pendiente', 'text-aqua-300' => in_array($p->status, ['contactado', 'en_proceso']), 'text-emerald-300' => $p->status === 'concretado', 'text-white/40' => $p->status === 'descartado'])>{{ $p->status_label }}</span>
                            </div>
                            <div class="mt-1 text-sm text-white/55">{{ \App\Models\SupportPledge::TYPES[$p->type]['icon'] ?? '' }} {{ $p->type_label }}@if ($p->amount) · ${{ number_format($p->amount, 0, ',', '.') }}@endif · {{ $p->created_at->translatedFormat('j M Y') }}</div>
                        </div>
                    @empty
                        <div class="card-dark p-8 text-center text-white/55">Todavía no has registrado intenciones de apoyo.</div>
                    @endforelse
                </div>

                @if ($comments->isNotEmpty())
                    <h2 class="mt-10 font-display text-2xl font-bold">Mis últimas opiniones</h2>
                    <div class="mt-4 space-y-2">
                        @foreach ($comments as $c)
                            <a href="{{ route('ventures.show', $c->venture) }}#opiniones" class="glass block rounded-2xl p-4 hover:border-lion-400/40">
                                <div class="text-sm font-semibold">{{ $c->venture->name }} @if ($c->rating)<span class="text-lion-400">{{ str_repeat('★', $c->rating) }}</span>@endif</div>
                                <p class="mt-1 line-clamp-2 text-sm text-white/60">{{ $c->body }}</p>
                                @unless ($c->is_approved)<p class="mt-1 text-xs text-lion-200">En revisión</p>@endunless
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts.site>
