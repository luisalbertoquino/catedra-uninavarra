<x-layouts.site :title="$venture->name" :description="$venture->tagline" :image="$venture->cover_url">
    @php
        $shareUrl = route('ventures.show', $venture);
        $shareText = "Vota por {$venture->name} en la Cátedra UNINAVARRA 🦁";
        $socials = array_filter(['Instagram' => $venture->instagram, 'Facebook' => $venture->facebook, 'TikTok' => $venture->tiktok, 'Sitio web' => $venture->website]);
        $voteProps = [
            'url' => route('ventures.vote', $venture),
            'voted' => $hasVoted,
            'count' => $venture->votes_count,
            'loginUrl' => auth()->guest() && $venture->edition->isVotingOpen() ? route('login') : null,
            'blocked' => auth()->check() ? $votingBlocked : (! $venture->edition->isVotingOpen() ? $votingBlocked : null),
        ];
    @endphp

    {{-- ================= HERO ================= --}}
    <section class="relative isolate overflow-hidden pt-16 lg:pt-20">
        <div class="absolute inset-0 -z-10">
            @if ($venture->cover_url)
                <img src="{{ $venture->cover_url }}" alt="" class="h-full w-full scale-110 object-cover opacity-40 blur-sm">
            @endif
            <div class="absolute inset-0 bg-gradient-to-b from-ink-950/60 via-ink-950/80 to-ink-950"></div>
        </div>

        <div class="container-x pb-12 pt-10 lg:pb-16 lg:pt-16">
            <nav class="mb-8 flex items-center gap-2 text-sm text-white/50">
                <a href="{{ route('ventures.index', ['edicion' => $venture->edition->slug]) }}" class="hover:text-white">← Emprendimientos {{ $venture->edition->period_label }}</a>
            </nav>

            <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="flex animate-fade-up flex-col gap-6 sm:flex-row sm:items-end">
                    @if ($venture->logo_url)
                        <img src="{{ $venture->logo_url }}" alt="Logo {{ $venture->name }}" class="h-28 w-28 rounded-3xl border border-white/20 bg-white object-contain p-2 shadow-2xl sm:h-32 sm:w-32">
                    @else
                        <span class="grid h-28 w-28 place-items-center rounded-3xl border border-white/20 bg-gradient-to-br from-lion-300 to-lion-600 font-display text-4xl font-bold text-ink-950 shadow-2xl shadow-lion-500/20 sm:h-32 sm:w-32">{{ $venture->initials }}</span>
                    @endif
                    <div>
                        <div class="flex flex-wrap gap-2">
                            @if ($venture->final_position === 1)<span class="chip border-transparent bg-lion-400 font-bold text-ink-950">🏆 Ganador {{ $venture->edition->period_label }}</span>
                            @elseif ($venture->final_position)<span class="chip border-lion-400/40 text-lion-200">🥇 Puesto {{ $venture->final_position }}</span>
                            @elseif ($venture->is_finalist)<span class="chip border-lion-400/40 text-lion-200">⭐ Finalista</span>@endif
                            @if ($venture->category)<a href="{{ route('ventures.index', ['categoria' => $venture->category->slug]) }}" class="chip hover:border-white/30">{{ $venture->category->icon }} {{ $venture->category->name }}</a>@endif
                            @if ($venture->academic_program)<span class="chip">🎓 {{ $venture->academic_program }}</span>@endif
                        </div>
                        <h1 class="mt-4 font-display text-4xl font-bold tracking-tight sm:text-6xl">{{ $venture->name }}</h1>
                        @if ($venture->tagline)<p class="mt-3 max-w-2xl text-lg text-white/70 sm:text-xl">{{ $venture->tagline }}</p>@endif
                    </div>
                </div>

                <div class="flex animate-fade-up flex-wrap items-center gap-3 [animation-delay:.15s]">
                    <div x-data="voteButton(@js($voteProps))">
                        <button type="button" @click="toggle" :disabled="loading"
                                class="btn px-6 py-3.5 text-base"
                                :class="voted ? 'bg-white text-ink-950 hover:bg-white/90' : 'bg-lion-400 text-ink-950 shadow-lg shadow-lion-500/30 hover:bg-lion-300'">
                            <svg class="h-5 w-5" :class="pulse && 'animate-pop'" viewBox="0 0 24 24" :fill="voted ? '#e11d48' : 'none'" stroke="currentColor" stroke-width="2"><path stroke-linejoin="round" d="M12 21s-7.5-4.6-9.5-9.3C1.1 8.4 3.2 4.5 7 4.5c2 0 3.6 1.1 5 2.9 1.4-1.8 3-2.9 5-2.9 3.8 0 5.9 3.9 4.5 7.2C19.5 16.4 12 21 12 21z"/></svg>
                            <span x-text="voted ? 'Votaste' : 'Votar'"></span>
                            <span class="rounded-full bg-ink-950/10 px-2 py-0.5 text-sm tabular-nums" x-text="count.toLocaleString('es-CO')"></span>
                        </button>
                    </div>
                    <a href="#apoyar" class="btn-ghost px-6 py-3.5 text-base">💼 Apoyar</a>
                    <div x-data="{ open: false, copied: false }" class="relative">
                        <button type="button" @click="navigator.share ? navigator.share({ title: @js($venture->name), text: @js($shareText), url: @js($shareUrl) }).catch(() => {}) : open = !open" @click.outside="open = false"
                                class="grid h-[3.25rem] w-[3.25rem] place-items-center rounded-full border border-white/15 hover:bg-white/5" aria-label="Compartir">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg>
                        </button>
                        <div x-cloak x-show="open" x-transition class="absolute right-0 z-20 mt-2 w-56 rounded-2xl border border-white/10 bg-ink-900 p-1 text-sm shadow-2xl">
                            <a target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($shareText.' '.$shareUrl) }}" class="block rounded-xl px-3 py-2 hover:bg-white/5">WhatsApp</a>
                            <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}" class="block rounded-xl px-3 py-2 hover:bg-white/5">Facebook</a>
                            <a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?text={{ rawurlencode($shareText) }}&url={{ rawurlencode($shareUrl) }}" class="block rounded-xl px-3 py-2 hover:bg-white/5">X / Twitter</a>
                            <a target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url={{ rawurlencode($shareUrl) }}" class="block rounded-xl px-3 py-2 hover:bg-white/5">LinkedIn</a>
                            <button type="button" @click="navigator.clipboard.writeText(@js($shareUrl)); copied = true; setTimeout(() => copied = false, 2000)" class="block w-full rounded-xl px-3 py-2 text-left hover:bg-white/5" x-text="copied ? '¡Enlace copiado!' : 'Copiar enlace'"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container-x grid gap-10 lg:grid-cols-12">
        {{-- ================= CONTENIDO ================= --}}
        <div class="space-y-14 lg:col-span-8">
            {{-- Video pitch --}}
            @if ($venture->video_embed_url)
                <section class="reveal">
                    <h2 class="eyebrow mb-4">▶ Video pitch</h2>
                    <div class="relative aspect-video overflow-hidden rounded-3xl border border-white/10 bg-black shadow-2xl shadow-volt-600/10">
                        <iframe src="{{ $venture->video_embed_url }}" title="Video pitch {{ $venture->name }}" class="absolute inset-0 h-full w-full" loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                </section>
            @elseif ($venture->video_url)
                <a href="{{ $venture->video_url }}" target="_blank" rel="noopener" class="card-dark card-dark-hover reveal flex items-center gap-4 p-6">
                    <span class="grid h-14 w-14 place-items-center rounded-full bg-lion-400 text-2xl text-ink-950">▶</span>
                    <span><span class="block font-semibold">Ver video pitch</span><span class="text-sm text-white/50">Se abrirá en una nueva pestaña</span></span>
                </a>
            @endif

            {{-- Descripción --}}
            @if ($venture->description)
                <section class="reveal">
                    <h2 class="font-display text-2xl font-bold sm:text-3xl">Sobre el proyecto</h2>
                    <div class="prose-dark mt-4 text-lg">{!! nl2br(e($venture->description)) !!}</div>
                </section>
            @endif

            {{-- Problema / Solución / Mercado --}}
            @if ($venture->problem || $venture->solution || $venture->target_market)
                <section class="grid gap-4 md:grid-cols-2">
                    @if ($venture->problem)
                        <div class="card-dark reveal relative overflow-hidden p-7">
                            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-rose-500/20 blur-2xl"></div>
                            <div class="text-3xl">🎯</div>
                            <h3 class="mt-4 font-display text-xl font-bold">El problema</h3>
                            <p class="mt-2 text-white/65">{!! nl2br(e($venture->problem)) !!}</p>
                        </div>
                    @endif
                    @if ($venture->solution)
                        <div class="card-dark reveal relative overflow-hidden p-7" style="transition-delay:.1s">
                            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-500/20 blur-2xl"></div>
                            <div class="text-3xl">💡</div>
                            <h3 class="mt-4 font-display text-xl font-bold">La solución</h3>
                            <p class="mt-2 text-white/65">{!! nl2br(e($venture->solution)) !!}</p>
                        </div>
                    @endif
                    @if ($venture->target_market)
                        <div class="card-dark reveal relative overflow-hidden p-7 md:col-span-2">
                            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-aqua-500/20 blur-2xl"></div>
                            <div class="text-3xl">🧭</div>
                            <h3 class="mt-4 font-display text-xl font-bold">Mercado objetivo</h3>
                            <p class="mt-2 text-white/65">{!! nl2br(e($venture->target_market)) !!}</p>
                        </div>
                    @endif
                </section>
            @endif

            {{-- Galería --}}
            @if ($venture->images->isNotEmpty())
                @php $gallery = $venture->images->map(fn ($i) => ['src' => $i->url, 'caption' => $i->caption])->values(); @endphp
                <section class="reveal" x-data="{ items: @js($gallery), current: null,
                        next() { this.current = (this.current + 1) % this.items.length },
                        prev() { this.current = (this.current - 1 + this.items.length) % this.items.length } }"
                         @keydown.escape.window="current = null" @keydown.arrow-right.window="current !== null && next()" @keydown.arrow-left.window="current !== null && prev()">
                    <h2 class="font-display text-2xl font-bold sm:text-3xl">Galería</h2>
                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($gallery as $i => $img)
                            <button type="button" @click="current = {{ $i }}" @class(['group relative overflow-hidden rounded-2xl border border-white/10', 'col-span-2 row-span-2 aspect-square' => $i === 0, 'aspect-square' => $i > 0])>
                                <img src="{{ $img['src'] }}" alt="{{ $img['caption'] ?? 'Imagen de '.$venture->name }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                <span class="absolute inset-0 bg-ink-950/0 transition group-hover:bg-ink-950/30"></span>
                            </button>
                        @endforeach
                    </div>

                    <template x-teleport="body">
                        <div x-cloak x-show="current !== null" x-transition.opacity class="fixed inset-0 z-[80] flex items-center justify-center bg-ink-950/95 p-4 backdrop-blur" @click.self="current = null">
                            <button type="button" @click="current = null" class="absolute right-4 top-4 grid h-11 w-11 place-items-center rounded-full bg-white/10 text-xl hover:bg-white/20" aria-label="Cerrar">✕</button>
                            <button type="button" @click="prev" class="absolute left-2 grid h-12 w-12 place-items-center rounded-full bg-white/10 text-2xl hover:bg-white/20 sm:left-6" aria-label="Anterior">‹</button>
                            <figure class="max-h-full max-w-5xl">
                                <img :src="current !== null ? items[current].src : ''" alt="" class="max-h-[80vh] rounded-2xl object-contain">
                                <figcaption class="mt-3 text-center text-sm text-white/60" x-text="current !== null ? (items[current].caption || `${current + 1} / ${items.length}`) : ''"></figcaption>
                            </figure>
                            <button type="button" @click="next" class="absolute right-2 grid h-12 w-12 place-items-center rounded-full bg-white/10 text-2xl hover:bg-white/20 sm:right-6" aria-label="Siguiente">›</button>
                        </div>
                    </template>
                </section>
            @endif

            {{-- Equipo --}}
            @if ($venture->members->isNotEmpty())
                <section class="reveal">
                    <h2 class="font-display text-2xl font-bold sm:text-3xl">El equipo</h2>
                    <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach ($venture->members as $m)
                            <div class="card-dark group p-4 text-center">
                                <x-avatar :name="$m->name" :src="$m->photo_url" size="xl" class="mx-auto ring-4 ring-white/5 transition group-hover:ring-lion-400/40" />
                                <div class="mt-3 text-sm font-semibold leading-tight">{{ $m->name }}</div>
                                @if ($m->role)<div class="mt-1 text-xs font-medium text-lion-300">{{ $m->role }}</div>@endif
                                @if ($m->academic_program)<div class="mt-1 text-[11px] leading-tight text-white/45">{{ $m->academic_program }}</div>@endif
                                @if ($m->linkedin)<a href="{{ $m->linkedin }}" target="_blank" rel="noopener" class="mt-2 inline-block text-xs text-aqua-300 hover:underline">LinkedIn ↗</a>@endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Opiniones --}}
            <section id="opiniones" class="scroll-mt-28">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="font-display text-2xl font-bold sm:text-3xl">Opiniones <span class="text-white/40">({{ $venture->comments_count }})</span></h2>
                    @if ($venture->rating_avg)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-lg text-lion-400">{{ str_repeat('★', (int) round($venture->rating_avg)) }}<span class="text-white/20">{{ str_repeat('★', 5 - (int) round($venture->rating_avg)) }}</span></span>
                            <span class="font-semibold">{{ number_format($venture->rating_avg, 1, ',', '.') }}</span><span class="text-white/50">/ 5</span>
                        </div>
                    @endif
                </div>

                @auth
                    <form method="POST" action="{{ route('ventures.comments.store', $venture) }}" x-data="{ rating: {{ (int) old('rating', 0) }}, hover: 0 }" class="card-dark mt-6 p-5 sm:p-6">
                        @csrf
                        <div class="flex items-start gap-4">
                            <x-avatar :name="auth()->user()->name" class="hidden sm:inline-grid" />
                            <div class="flex-1">
                                <div class="mb-3 flex items-center gap-3">
                                    <span class="text-sm text-white/60">Tu calificación:</span>
                                    <div class="flex text-2xl" @mouseleave="hover = 0">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <button type="button" @click="rating = {{ $s }}" @mouseenter="hover = {{ $s }}" class="px-0.5 transition hover:scale-125"
                                                    :class="(hover || rating) >= {{ $s }} ? 'text-lion-400' : 'text-white/20'" aria-label="{{ $s }} estrellas">★</button>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating" :value="rating || ''">
                                </div>
                                <textarea name="body" rows="3" required maxlength="1000" class="input-dark" placeholder="¿Qué te parece la idea? Tu retroalimentación ayuda al equipo a mejorar…">{{ old('body') }}</textarea>
                                <x-input-error :messages="$errors->get('body')" class="mt-2" />
                                <div class="mt-3 flex justify-end"><button class="btn-lion">Publicar opinión</button></div>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="card-dark mt-6 flex flex-col items-center justify-between gap-4 p-6 sm:flex-row">
                        <p class="text-white/70">Inicia sesión para calificar y dejar tu opinión.</p>
                        <div class="flex gap-2"><a href="{{ route('login') }}" class="btn-ghost">Ingresar</a><a href="{{ route('register') }}" class="btn-lion">Crear cuenta</a></div>
                    </div>
                @endauth

                <div class="mt-6 space-y-3">
                    @forelse ($comments as $c)
                        <article class="glass flex gap-4 rounded-2xl p-5">
                            <x-avatar :name="$c->user->name" />
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <span class="font-semibold">{{ $c->user->name }}</span>
                                    @if ($c->user->isInvestor())<span class="chip !py-0 text-[10px] text-aqua-300">💼 Aliado</span>@endif
                                    @if ($c->rating)<span class="text-sm text-lion-400">{{ str_repeat('★', $c->rating) }}</span>@endif
                                    <span class="text-xs text-white/40">{{ $c->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-1.5 break-words text-white/75">{{ $c->body }}</p>
                                @if (auth()->id() === $c->user_id)
                                    <form method="POST" action="{{ route('comments.destroy', $c) }}" class="mt-2" onsubmit="return confirm('¿Eliminar tu comentario?')">@csrf @method('DELETE')
                                        <button class="text-xs text-white/40 hover:text-rose-300">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="py-8 text-center text-white/50">Aún no hay opiniones. ¡Sé el primero en comentar! 💬</p>
                    @endforelse
                    <div class="pt-4">{{ $comments->fragment('opiniones')->links('vendor.pagination.dark') }}</div>
                </div>
            </section>

            {{-- Apoyar --}}
            <section id="apoyar" class="scroll-mt-28">
                <div class="relative overflow-hidden rounded-[2rem] border border-lion-400/20 bg-gradient-to-br from-lion-400/[.12] via-ink-900 to-volt-600/[.12] p-6 sm:p-10">
                    <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-lion-400/20 blur-3xl"></div>
                    <div class="relative">
                        <span class="eyebrow">✦ Impulsa este emprendimiento</span>
                        <h2 class="mt-3 font-display text-3xl font-bold sm:text-4xl">¿Quieres apoyar a {{ $venture->name }}?</h2>
                        <p class="mt-3 max-w-2xl text-white/65">Registra tu intención de apoyo. <b class="text-white/85">No se realiza ningún cobro en línea:</b> el equipo de la Cátedra UNINAVARRA revisa tu solicitud y te pone en contacto con los emprendedores.</p>
                        @if ($venture->funding_goal)
                            <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/5 px-4 py-2 text-sm">🎯 Meta de financiación: <b class="text-lion-300">${{ number_format($venture->funding_goal, 0, ',', '.') }} COP</b></p>
                        @endif

                        @auth
                            <form method="POST" action="{{ route('ventures.pledge', $venture) }}" x-data="{ type: @js(old('type', auth()->user()->isInvestor() ? 'inversion' : 'mentoria')) }" class="mt-8 space-y-5">
                                @csrf
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                                    @foreach ($pledgeTypes as $key => $t)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="type" value="{{ $key }}" x-model="type" class="peer sr-only">
                                            <span class="flex h-full flex-col items-center rounded-2xl border border-white/10 bg-white/[.03] p-4 text-center transition peer-checked:border-lion-400 peer-checked:bg-lion-400/10 peer-focus-visible:ring-2 peer-focus-visible:ring-lion-400 hover:border-white/30">
                                                <span class="text-2xl">{{ $t['icon'] }}</span>
                                                <span class="mt-2 text-sm font-semibold">{{ $t['label'] }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @foreach ($pledgeTypes as $key => $t)
                                    <p x-cloak x-show="type === '{{ $key }}'" class="text-sm text-white/55">{{ $t['hint'] }}</p>
                                @endforeach

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div x-show="type === 'inversion' || type === 'donacion'" x-transition>
                                        <label class="label-dark" for="amount">Monto aproximado (COP) <span class="text-white/40">· opcional</span></label>
                                        <input id="amount" type="number" name="amount" min="0" step="50000" value="{{ old('amount') }}" class="input-dark" placeholder="Ej. 2000000">
                                    </div>
                                    @unless (auth()->user()->phone)
                                        <div>
                                            <label class="label-dark" for="phone">Teléfono de contacto</label>
                                            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" class="input-dark" placeholder="300 000 0000">
                                        </div>
                                    @endunless
                                </div>
                                <div>
                                    <label class="label-dark" for="message">Mensaje para el equipo</label>
                                    <textarea id="message" name="message" rows="4" required minlength="10" maxlength="2000" class="input-dark" placeholder="Cuéntales quién eres y cómo te gustaría apoyarlos…">{{ old('message') }}</textarea>
                                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                                </div>
                                <button class="btn-lion px-7 py-3.5 text-base">Enviar intención de apoyo →</button>
                            </form>
                        @else
                            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                                <a href="{{ route('register', ['perfil' => 'inversionista']) }}" class="btn-lion px-7 py-3.5">Registrarme como aliado</a>
                                <a href="{{ route('login') }}" class="btn-ghost px-7 py-3.5">Ya tengo cuenta</a>
                            </div>
                        @endauth
                    </div>
                </div>
            </section>
        </div>

        {{-- ================= SIDEBAR ================= --}}
        <aside class="lg:col-span-4">
            <div class="space-y-4 lg:sticky lg:top-28">
                <div class="card-dark p-6">
                    <div class="grid grid-cols-3 divide-x divide-white/10 text-center">
                        <div x-data="{ c: {{ $venture->votes_count }} }" @vote-updated.window="c = $event.detail">
                            <div class="font-display text-2xl font-bold text-lion-300" x-text="c.toLocaleString('es-CO')">{{ number_format($venture->votes_count, 0, ',', '.') }}</div>
                            <div class="text-xs text-white/50">votos</div>
                        </div>
                        <div>
                            <div class="font-display text-2xl font-bold">{{ $venture->rating_avg ? number_format($venture->rating_avg, 1, ',', '.') : '–' }}</div>
                            <div class="text-xs text-white/50">calificación</div>
                        </div>
                        <div>
                            <div class="font-display text-2xl font-bold">{{ number_format($venture->views, 0, ',', '.') }}</div>
                            <div class="text-xs text-white/50">visitas</div>
                        </div>
                    </div>

                    @php $status = $venture->edition->votingStatus(); @endphp
                    <div class="mt-6 rounded-2xl bg-white/[.04] p-4 text-sm">
                        @if ($status === 'abierta')
                            <p class="font-semibold text-emerald-300">● Votación abierta</p>
                            <p class="mt-1 text-white/60">Cierra el {{ $venture->edition->voting_ends_at->translatedFormat('j \d\e F, g:i a') }}.
                                @if ($votesLeft !== null) Te quedan <b class="text-white">{{ $votesLeft }}</b> voto(s).@endif
                            </p>
                        @elseif ($status === 'proxima')
                            <p class="font-semibold text-aqua-300">Votación próximamente</p>
                            <p class="mt-1 text-white/60">Abre el {{ $venture->edition->voting_starts_at->translatedFormat('j \d\e F') }}.</p>
                        @else
                            <p class="font-semibold text-white/70">Votación cerrada</p>
                        @endif
                        @if ($votingBlocked && auth()->check() && $status === 'abierta')
                            <p class="mt-2 text-lion-200">⚠ {{ $votingBlocked }}</p>
                        @endif
                    </div>
                </div>

                <div class="card-dark p-6">
                    <h3 class="font-display text-lg font-bold">Contacto</h3>
                    @if ($venture->contact_name)<p class="mt-1 text-sm text-white/55">{{ $venture->contact_name }}</p>@endif
                    <div class="mt-4 grid gap-2">
                        @if ($venture->whatsapp_url)
                            <a href="{{ $venture->whatsapp_url }}" target="_blank" rel="noopener" class="btn bg-emerald-500 text-white hover:bg-emerald-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.1-.2.3-.8 1-.9 1.2-.2.2-.3.2-.6.1-.3-.1-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.4-.5c.2-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.1.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3zM12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2z"/></svg>
                                WhatsApp
                            </a>
                        @endif
                        @if ($venture->contact_email)
                            <a href="mailto:{{ $venture->contact_email }}" class="btn-ghost">✉ {{ $venture->contact_email }}</a>
                        @endif
                    </div>
                    @if ($socials)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($socials as $label => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="chip hover:border-white/30 hover:text-white">{{ $label }} ↗</a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="card-dark p-6 text-sm">
                    <div class="flex justify-between py-1.5"><span class="text-white/50">Edición</span><a href="{{ route('editions.show', $venture->edition) }}" class="font-medium hover:text-lion-300">{{ $venture->edition->name }}</a></div>
                    <div class="flex justify-between py-1.5"><span class="text-white/50">Intenciones de apoyo</span><span class="font-medium">{{ $venture->pledges_count }}</span></div>
                    <div class="flex justify-between py-1.5"><span class="text-white/50">Integrantes</span><span class="font-medium">{{ $venture->members->count() }}</span></div>
                </div>
            </div>
        </aside>
    </div>

    {{-- Relacionados --}}
    @if ($related->isNotEmpty())
        <section class="container-x mt-24">
            <h2 class="section-title !text-3xl">También te puede interesar</h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $r)
                    <x-venture-card :venture="$r" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.site>
