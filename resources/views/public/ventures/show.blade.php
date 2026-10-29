<x-layouts.site :title="$venture->name" :description="$venture->tagline" :image="$venture->cover_url">
    @php
        $shareUrl = route('ventures.show', $venture);
        $shareText = "Vota por {$venture->name} en Cátedra UNINAVARRA";
        $socials = array_filter([
            'instagram' => ['Instagram', 'brands', $venture->instagram],
            'facebook' => ['Facebook', 'brands', $venture->facebook],
            'tiktok' => ['TikTok', 'brands', $venture->tiktok],
            'globe' => ['Sitio web', 'solid', $venture->website],
        ], fn ($s) => filled($s[2]));
        $voteProps = [
            'url' => route('ventures.vote', $venture),
            'voted' => $hasVoted,
            'count' => $venture->votes_count,
            'loginUrl' => auth()->guest() && $venture->edition->isVotingOpen() ? route('login') : null,
            'blocked' => auth()->check() ? $votingBlocked : (! $venture->edition->isVotingOpen() ? $votingBlocked : null),
            'review' => $myReview ? ['rating' => (int) $myReview->rating, 'body' => $myReview->body] : null,
            'minChars' => \App\Http\Controllers\VoteController::MIN_COMMENT,
        ];
    @endphp

    <div x-data="voteWizard(@js($voteProps))">
    {{-- ================= ENCABEZADO ================= --}}
    <section class="ecos-g-violeta ecos-band pb-12 pt-24 lg:pb-16 lg:pt-28">
        <div class="container-x">
            <nav class="mb-6 text-sm">
                <a href="{{ route('ventures.index', ['edicion' => $venture->edition->slug]) }}" class="inline-flex items-center gap-2 rounded-full bg-white/70 px-4 py-2 font-medium text-lion-700 backdrop-blur hover:bg-white"><x-icon name="arrow-left" /> Emprendimientos {{ $venture->edition->period_label }}</a>
            </nav>

            <div class="tarjeta-ecos animate-fade-up">
                <span class="tarjeta-ecos__tab">
                    @if ($venture->category?->icon)<x-icon :name="$venture->category->icon" />@endif
                    {{ $venture->category?->name ?? 'Emprendimiento' }} · {{ $venture->edition->period_label }}
                </span>
                <div class="tarjeta-ecos__body flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                        @if ($venture->logo_url)
                            <img src="{{ $venture->logo_url }}" alt="Logo {{ $venture->name }}" class="h-28 w-28 shrink-0 rounded-ecos bg-white object-contain p-2 shadow-lg shadow-lion-900/10 sm:h-32 sm:w-32">
                        @else
                            <span class="grid h-28 w-28 shrink-0 place-items-center rounded-ecos bg-gradient-to-br from-lion-400 to-magenta-500 font-display text-4xl font-medium text-white sm:h-32 sm:w-32">{{ $venture->initials }}</span>
                        @endif
                        <div>
                            <div class="flex flex-wrap gap-2">
                                @if ($venture->final_position === 1)<span class="inline-flex items-center gap-1.5 rounded-full bg-lion-600 px-3 py-1 text-xs font-bold text-white"><x-icon name="trophy" class="!text-white" /> Ganador {{ $venture->edition->period_label }}</span>
                                @elseif ($venture->final_position)<span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-lion-700"><x-icon name="medal" /> Puesto {{ $venture->final_position }}</span>
                                @elseif ($venture->is_finalist)<span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-lion-700"><x-icon name="star" /> Finalista</span>@endif
                                @if ($venture->academic_program)<span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-medium text-ink-700"><x-icon name="graduation-cap" /> {{ $venture->academic_program }}</span>@endif
                            </div>
                            <h1 class="page-title mt-3">{{ $venture->name }}</h1>
                            @if ($venture->tagline)<p class="mt-3 max-w-2xl text-lg text-ink-700">{{ $venture->tagline }}</p>@endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" @click="start" :disabled="loading"
                                class="btn px-6 py-3.5 text-base"
                                :class="voted ? 'border border-lion-300 bg-white text-lion-700 hover:bg-lion-50' : 'bg-lion-600 text-white shadow-lg shadow-lion-500/30 hover:bg-lion-600'">
                            <i class="fa-heart text-lg" :class="[voted ? 'fa-solid' : 'fa-regular', pulse && 'animate-pop']" aria-hidden="true"></i>
                            <span x-text="voted ? 'Votaste' : 'Votar'"></span>
                            <span class="rounded-full bg-black/10 px-2 py-0.5 text-sm tabular-nums" x-text="count.toLocaleString('es-CO')"></span>
                        </button>
                        <a href="#apoyar" class="btn-ghost bg-white px-6 py-3.5 text-base"><x-icon name="hand-holding-heart" /> Apoyar</a>
                        <div x-data="{ open: false, copied: false }" class="relative">
                            <button type="button" @click="navigator.share ? navigator.share({ title: @js($venture->name), text: @js($shareText), url: @js($shareUrl) }).catch(() => {}) : open = !open" @click.outside="open = false"
                                    class="grid h-[3.25rem] w-[3.25rem] place-items-center rounded-full bg-white hover:bg-lion-50" aria-label="Compartir">
                                <x-icon name="share-nodes" class="text-lg" />
                            </button>
                            <div x-cloak x-show="open" x-transition class="absolute right-0 z-20 mt-2 w-56 rounded-ecos border border-ink-900/[.06] bg-white p-1 text-sm text-ink-800 shadow-2xl">
                                <a target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($shareText.' '.$shareUrl) }}" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-tarjeta"><x-icon name="whatsapp" style="brands" fw /> WhatsApp</a>
                                <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-tarjeta"><x-icon name="facebook" style="brands" fw /> Facebook</a>
                                <a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?text={{ rawurlencode($shareText) }}&url={{ rawurlencode($shareUrl) }}" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-tarjeta"><x-icon name="x-twitter" style="brands" fw /> X</a>
                                <a target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url={{ rawurlencode($shareUrl) }}" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-tarjeta"><x-icon name="linkedin" style="brands" fw /> LinkedIn</a>
                                <button type="button" @click="navigator.clipboard.writeText(@js($shareUrl)); copied = true; setTimeout(() => copied = false, 2000)" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left hover:bg-tarjeta"><x-icon name="link" fw /><span x-text="copied ? '¡Enlace copiado!' : 'Copiar enlace'"></span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container-x mt-12 grid gap-10 lg:grid-cols-12 [&>*]:min-w-0">
        {{-- ================= CONTENIDO ================= --}}
        <div class="space-y-14 lg:col-span-8">
            {{-- Video pitch --}}
            @if ($venture->video_embed_url)
                <section class="reveal">
                    <h2 class="eyebrow mb-4"><x-icon name="circle-play" /> Video pitch</h2>
                    <div class="relative aspect-video overflow-hidden rounded-3xl border border-white/10 bg-black shadow-2xl shadow-volt-600/10">
                        <iframe src="{{ $venture->video_embed_url }}" title="Video pitch {{ $venture->name }}" class="absolute inset-0 h-full w-full" loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                </section>
            @elseif ($venture->video_url)
                <a href="{{ $venture->video_url }}" target="_blank" rel="noopener" class="card-dark card-dark-hover reveal flex items-center gap-4 p-6">
                    <span class="grid h-14 w-14 place-items-center rounded-full border border-lion-400/30 bg-lion-400/10"><x-icon name="play" class="text-xl" /></span>
                    <span><span class="block font-semibold">Ver video pitch</span><span class="text-sm text-white/50">Se abrirá en una nueva pestaña</span></span>
                </a>
            @endif

            {{-- Descripción --}}
            @if ($venture->description && trim($venture->description) !== trim((string) $venture->tagline))
                <section class="reveal">
                    <h2 class="font-display text-2xl font-semibold sm:text-3xl">Sobre el proyecto</h2>
                    <div class="prose-dark mt-4 text-lg">{!! nl2br(e($venture->description)) !!}</div>
                </section>
            @endif

            {{-- Problema, solución, misión, visión y población objetivo --}}
            @php
                $blocks = collect([
                    ['bullseye', 'El problema', $venture->problem],
                    ['lightbulb', 'La solución', $venture->solution],
                    ['flag', 'Misión', $venture->mission],
                    ['binoculars', 'Visión', $venture->vision],
                    ['users-viewfinder', 'Población objetivo', $venture->target_market],
                ])->filter(fn ($b) => filled($b[2]))->values();
            @endphp
            @if ($blocks->isNotEmpty())
                <section class="grid gap-4 md:grid-cols-2">
                    @foreach ($blocks as $i => [$icon, $label, $text])
                        <div @class(['card-dark reveal relative overflow-hidden p-7', 'md:col-span-2' => $loop->last && $blocks->count() % 2 === 1])>
                            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-lion-400/10 blur-2xl"></div>
                            <span class="grid h-12 w-12 place-items-center rounded-xl bg-lion-400/10"><x-icon :name="$icon" class="text-xl" /></span>
                            <h3 class="mt-4 font-display text-xl font-semibold">{{ $label }}</h3>
                            <p class="mt-2 text-white/65">{!! nl2br(e($text)) !!}</p>
                        </div>
                    @endforeach
                </section>
            @endif

            {{-- Montos del plan de negocio --}}
            @if ($venture->startup_amount || $venture->funding_goal)
                <section class="reveal grid gap-4 sm:grid-cols-2">
                    @if ($venture->startup_amount)
                        <div class="card-dark p-6">
                            <div class="text-sm text-white/55"><x-icon name="seedling" /> Monto estimado para iniciar</div>
                            <div class="mt-2 font-display text-3xl font-semibold">${{ number_format($venture->startup_amount, 0, ',', '.') }} <span class="text-base text-white/40">COP</span></div>
                        </div>
                    @endif
                    @if ($venture->funding_goal)
                        <div class="card-dark border-lion-400/30 p-6">
                            <div class="text-sm text-white/55"><x-icon name="hand-holding-dollar" /> Monto que buscan con inversionistas</div>
                            <div class="mt-2 font-display text-3xl font-semibold text-lion-300">${{ number_format($venture->funding_goal, 0, ',', '.') }} <span class="text-base text-white/40">COP</span></div>
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
                    <h2 class="font-display text-2xl font-semibold sm:text-3xl">Galería</h2>
                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($gallery as $i => $img)
                            <button type="button" @click="current = {{ $i }}" @class(['group relative overflow-hidden rounded-2xl border border-white/10', 'col-span-2 row-span-2 aspect-square' => $i === 0, 'col-span-2 aspect-[2/1]' => $i > 0 && $loop->last && $loop->count % 2 === 0, 'aspect-square' => $i > 0 && ! ($loop->last && $loop->count % 2 === 0)])>
                                <img src="{{ $img['src'] }}" alt="{{ $img['caption'] ?? 'Imagen de '.$venture->name }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                <span class="absolute inset-0 bg-ink-950/0 transition group-hover:bg-ink-950/30"></span>
                            </button>
                        @endforeach
                    </div>

                    <template x-teleport="body">
                        <div x-cloak x-show="current !== null" x-transition.opacity class="fixed inset-0 z-[80] flex items-center justify-center bg-ink-950/95 p-4 backdrop-blur" @click.self="current = null">
                            <button type="button" @click="current = null" class="absolute right-4 top-4 grid h-11 w-11 place-items-center rounded-lg bg-white/10 text-xl hover:bg-white/20" aria-label="Cerrar"><x-icon name="xmark" /></button>
                            <button type="button" @click="prev" class="absolute left-2 grid h-12 w-12 place-items-center rounded-lg bg-white/10 text-2xl hover:bg-white/20 sm:left-6" aria-label="Anterior"><x-icon name="chevron-left" class="text-lg" /></button>
                            <figure class="max-h-full max-w-5xl">
                                <img :src="current !== null ? items[current].src : ''" alt="" class="max-h-[80vh] rounded-2xl object-contain">
                                <figcaption class="mt-3 text-center text-sm text-white/60" x-text="current !== null ? (items[current].caption || `${current + 1} / ${items.length}`) : ''"></figcaption>
                            </figure>
                            <button type="button" @click="next" class="absolute right-2 grid h-12 w-12 place-items-center rounded-lg bg-white/10 text-2xl hover:bg-white/20 sm:right-6" aria-label="Siguiente"><x-icon name="chevron-right" class="text-lg" /></button>
                        </div>
                    </template>
                </section>
            @endif

            {{-- Equipo --}}
            @if ($venture->members->isNotEmpty())
                <section class="reveal">
                    <h2 class="font-display text-2xl font-semibold sm:text-3xl">El equipo</h2>
                    <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach ($venture->members as $m)
                            <div class="card-dark group p-4 text-center">
                                <x-avatar :name="$m->name" :src="$m->photo_url" size="xl" class="mx-auto ring-4 ring-white/5 transition group-hover:ring-lion-400/40" />
                                <div class="mt-3 text-sm font-semibold leading-tight">{{ $m->name }}</div>
                                @if ($m->role)<div class="mt-1 text-xs font-medium text-lion-300">{{ $m->role }}</div>@endif
                                @if ($m->academic_program)<div class="mt-1 text-[11px] leading-tight text-white/45">{{ $m->academic_program }}</div>@endif
                                @if ($m->linkedin)<a href="{{ $m->linkedin }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-1.5 text-xs text-white/70 hover:text-white"><x-icon name="linkedin" style="brands" /> LinkedIn</a>@endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Opiniones --}}
            <section id="opiniones" class="scroll-mt-28">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="font-display text-2xl font-semibold sm:text-3xl">Opiniones <span class="text-white/40">({{ $venture->comments_count }})</span></h2>
                    @if ($venture->rating_avg)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="flex gap-0.5">@for ($s = 1; $s <= 5; $s++)<x-icon name="star" :style="$s <= round($venture->rating_avg) ? 'solid' : 'regular'" />@endfor</span>
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
                                    <div class="flex text-xl" @mouseleave="hover = 0">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <button type="button" @click="rating = {{ $s }}" @mouseenter="hover = {{ $s }}" class="px-0.5 transition hover:scale-125" aria-label="{{ $s }} estrellas">
                                                <i class="ico fa-star" :class="(hover || rating) >= {{ $s }} ? 'fa-solid' : 'fa-regular'" aria-hidden="true"></i>
                                            </button>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating" :value="rating || ''">
                                </div>
                                <textarea name="body" rows="3" required maxlength="1000" class="input-dark" placeholder="¿Qué te parece la idea? Tu retroalimentación ayuda al equipo a mejorar…">{{ old('body') }}</textarea>
                                <x-input-error :messages="$errors->get('body')" class="mt-2" />
                                <div class="mt-3 flex justify-end"><button class="btn-lion"><x-icon name="paper-plane" /> Publicar opinión</button></div>
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
                                    @if ($c->user->isInvestor())<span class="chip !py-0 text-[10px]"><x-icon name="briefcase" /> Aliado</span>@endif
                                    @if ($c->rating)<span class="flex gap-0.5 text-xs">@for ($s = 1; $s <= 5; $s++)<x-icon name="star" :style="$s <= $c->rating ? 'solid' : 'regular'" />@endfor</span>@endif
                                    <span class="text-xs text-white/40">{{ $c->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-1.5 break-words text-white/75">{{ $c->body }}</p>
                                @if (auth()->id() === $c->user_id)
                                    <form method="POST" action="{{ route('comments.destroy', $c) }}" class="mt-2" onsubmit="return confirm('¿Eliminar tu comentario?')">@csrf @method('DELETE')
                                        <button class="inline-flex items-center gap-1.5 text-xs text-white/40 hover:text-white"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="py-8 text-center text-white/50">Aún no hay opiniones. ¡Sé el primero en comentar!</p>
                    @endforelse
                    <div class="pt-4">{{ $comments->fragment('opiniones')->links('vendor.pagination.dark') }}</div>
                </div>
            </section>

            {{-- Apoyar --}}
            <section id="apoyar" class="scroll-mt-28">
                <div class="ecos-soft-coral relative overflow-hidden rounded-ecos p-6 sm:p-10">
                    <div class="relative">
                        <span class="eyebrow"><x-icon name="rocket" /> Impulsa este emprendimiento</span>
                        <h2 class="mt-3 font-display text-3xl font-semibold sm:text-4xl">¿Quieres apoyar a {{ $venture->name }}?</h2>
                        <p class="mt-3 max-w-2xl text-white/65">Registra tu intención de apoyo. <b class="text-white/85">No se realiza ningún cobro en línea:</b> el Centro de Emprendimiento revisa tu solicitud, te contacta y te pone en contacto con los emprendedores. Cuando el apoyo se concreta, aparece en el ranking de inversionistas.</p>
                        <x-investment-contact compact class="mt-5 max-w-xl" />
                        @if ($venture->funding_goal)
                            <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/5 px-4 py-2 text-sm"><x-icon name="bullseye" /> Meta de financiación: <b class="text-lion-300">${{ number_format($venture->funding_goal, 0, ',', '.') }} COP</b></p>
                        @endif

                        @auth
                            @php $oldTypes = old('types', auth()->user()->isInvestor() ? ['inversion'] : []); @endphp
                            <form method="POST" action="{{ route('ventures.pledge', $venture) }}" x-data="{ types: @js($oldTypes) }" class="mt-8 space-y-5">
                                @csrf
                                <p class="text-sm font-semibold text-lion-700">¿Cómo quieres apoyarlos? <span class="font-normal text-ink-500">Puedes elegir varias opciones.</span></p>
                                <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-4">
                                    @foreach ($pledgeTypes as $key => $t)
                                        <label class="cursor-pointer" title="{{ $t['hint'] }}">
                                            <input type="checkbox" name="types[]" value="{{ $key }}" x-model="types" class="peer sr-only">
                                            <span class="flex h-full items-center gap-2.5 rounded-ecos border-2 border-transparent bg-white p-3 text-left transition peer-checked:border-lion-500 peer-checked:bg-lion-50 peer-focus-visible:ring-2 peer-focus-visible:ring-lion-400 hover:border-lion-200">
                                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-tarjeta"><x-icon :name="$t['icon']" /></span>
                                                <span class="text-sm font-semibold leading-tight text-ink-800">{{ $t['label'] }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <x-input-error :messages="$errors->get('types')" class="mt-1" />

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div x-show="types.includes('inversion') || types.includes('donacion')" x-transition>
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
                                </div>
                                <label class="flex items-start gap-3 text-sm text-white/70">
                                    <input type="checkbox" name="public_ranking" value="1" @checked(old('public_ranking', auth()->user()->show_in_investor_ranking))
                                           class="mt-0.5 rounded border-white/20 bg-white/5 text-lion-400 focus:ring-lion-400 focus:ring-offset-ink-950">
                                    <span>Mostrar mi nombre u organización en el <a href="{{ route('ranking', ['vista' => 'inversionistas']) }}" class="font-semibold text-lion-300 hover:text-lion-200">ranking de inversionistas</a>.</span>
                                </label>
                                <button class="btn-lion px-7 py-3.5 text-base" :disabled="!types.length">Enviar intención de apoyo <x-icon name="paper-plane" /></button>
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
                            <div class="font-display text-2xl font-semibold text-lion-300" x-text="c.toLocaleString('es-CO')">{{ number_format($venture->votes_count, 0, ',', '.') }}</div>
                            <div class="text-xs text-white/50">votos</div>
                        </div>
                        <div>
                            <div class="font-display text-2xl font-semibold">{{ $venture->rating_avg ? number_format($venture->rating_avg, 1, ',', '.') : '–' }}</div>
                            <div class="text-xs text-white/50">calificación</div>
                        </div>
                        <div>
                            <div class="font-display text-2xl font-semibold">{{ number_format($venture->views, 0, ',', '.') }}</div>
                            <div class="text-xs text-white/50">visitas</div>
                        </div>
                    </div>

                    @php $status = $venture->edition->votingStatus(); @endphp
                    <div class="mt-6 rounded-2xl bg-white/[.04] p-4 text-sm">
                        @if ($status === 'abierta')
                            <p class="flex items-center gap-2 font-semibold text-white"><x-icon name="circle-check" /> Votación abierta</p>
                            <p class="mt-1 text-white/60">Cierra el {{ $venture->edition->voting_ends_at->translatedFormat('j \d\e F, g:i a') }}.
                                @if ($votesLeft !== null && ! $votingBlocked)
                                    {{ $hasVoted ? 'Ya votaste por este emprendimiento.' : ($votesLeft ? 'Tienes tu voto disponible.' : 'Ya usaste tu voto en otro emprendimiento.') }}
                                @endif
                            </p>
                        @elseif ($status === 'proxima')
                            <p class="flex items-center gap-2 font-semibold text-white"><x-icon name="hourglass-half" /> Votación próximamente</p>
                            <p class="mt-1 text-white/60">Abre el {{ $venture->edition->voting_starts_at->translatedFormat('j \d\e F') }}.</p>
                        @else
                            <p class="flex items-center gap-2 font-semibold text-white/70"><x-icon name="lock" /> Votación cerrada</p>
                        @endif
                        @if ($votingBlocked && auth()->check() && $status === 'abierta')
                            <p class="mt-2 flex gap-2 text-lion-200"><x-icon name="triangle-exclamation" class="mt-0.5" /> {{ $votingBlocked }}</p>
                        @endif
                    </div>
                </div>

                @if (! $venture->whatsapp_url && ! $venture->contact_email && ! $socials)
                    {{-- El equipo aún no tiene canales propios: el contacto pasa por el Centro de Emprendimiento --}}
                    <x-investment-contact compact />
                @else
                <div class="card-dark p-6">
                    <h3 class="font-display text-lg font-semibold">Contacto</h3>
                    @if ($venture->contact_name)<p class="mt-1 text-sm text-white/55">{{ $venture->contact_name }}</p>@endif
                    <div class="mt-4 grid gap-2">
                        @if ($venture->whatsapp_url)
                            <a href="{{ $venture->whatsapp_url }}" target="_blank" rel="noopener" class="btn bg-emerald-700 text-white hover:bg-emerald-600">
                                <x-icon name="whatsapp" style="brands" class="text-lg" />
                                WhatsApp
                            </a>
                        @endif
                        @if ($venture->contact_email)
                            <a href="mailto:{{ $venture->contact_email }}" class="btn-ghost"><x-icon name="envelope" /> {{ $venture->contact_email }}</a>
                        @endif
                    </div>
                    @if ($socials)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($socials as $icon => [$label, $iconStyle, $url])
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="chip !px-3 !py-1.5 hover:border-lion-400/40 hover:text-white"><x-icon :name="$icon" :style="$iconStyle" /> {{ $label }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif

                <div class="card-dark p-6 text-sm">
                    @if ($venture->team_group)<div class="flex justify-between py-1.5"><span class="text-white/50">Grupo</span><span class="font-medium">{{ $venture->team_group }}</span></div>@endif
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
    {{-- ================= VOTO PASO A PASO ================= --}}
    <template x-teleport="body">
        <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-[90] flex items-end justify-center bg-lion-900/50 p-0 backdrop-blur-sm sm:items-center sm:p-4"
             @keydown.escape.window="open = false" @click.self="open = false" role="dialog" aria-modal="true" aria-labelledby="vote-title">
            <div class="surface-light w-full max-w-lg rounded-t-ecos bg-white p-6 shadow-2xl sm:rounded-ecos sm:p-8" x-show="open" x-transition>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="eyebrow" x-show="step < 4">Paso <span x-text="step"></span> de 3</p>
                        <h2 id="vote-title" class="mt-1 font-display text-2xl text-lion-700">
                            <span x-show="step === 1">¿Qué te parece {{ $venture->name }}?</span>
                            <span x-show="step === 2" x-cloak>Cuéntale al equipo por qué</span>
                            <span x-show="step === 3" x-cloak>Confirma tu voto</span>
                            <span x-show="step === 4" x-cloak>¡Gracias por votar!</span>
                        </h2>
                    </div>
                    <button type="button" @click="open = false" class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-tarjeta text-ink-600 hover:bg-lion-100" aria-label="Cerrar"><x-icon name="xmark" /></button>
                </div>
                <div class="mt-4 flex gap-1.5" x-show="step < 4" aria-hidden="true">
                    <template x-for="n in 3"><span class="h-1.5 flex-1 rounded-full transition" :class="n <= step ? 'bg-lion-500' : 'bg-tarjeta'"></span></template>
                </div>

                {{-- 1. Estrellas --}}
                <div x-show="step === 1" class="mt-6 text-center">
                    <div class="inline-flex text-4xl" @mouseleave="hover = 0">
                        <template x-for="n in 5">
                            <button type="button" @click="rating = n; error = ''" @mouseenter="hover = n" class="px-1 transition hover:scale-110" :aria-label="n + ' estrellas'">
                                <i class="fa-star text-lion-500" :class="(hover || rating) >= n ? 'fa-solid' : 'fa-regular'" aria-hidden="true"></i>
                            </button>
                        </template>
                    </div>
                    <p class="mt-2 h-5 text-sm font-semibold text-lion-700" x-text="labels[hover || rating]"></p>
                </div>

                {{-- 2. Comentario --}}
                <div x-show="step === 2" x-cloak class="mt-6">
                    <textarea x-model="body" @input="error = ''" rows="5" maxlength="1000" class="input-dark" placeholder="¿Qué te gustó? ¿Qué le recomendarías mejorar?"></textarea>
                    <p class="mt-1.5 text-right text-xs" :class="chars >= minChars ? 'text-aqua-600' : 'text-ink-500'"><span x-text="chars"></span> / mínimo <span x-text="minChars"></span> caracteres</p>
                </div>

                {{-- 3. Confirmar --}}
                <div x-show="step === 3" x-cloak class="mt-6 rounded-ecos bg-tarjeta p-5">
                    <div class="flex gap-0.5 text-lion-500"><template x-for="n in 5"><i class="fa-star" :class="rating >= n ? 'fa-solid' : 'fa-regular'" aria-hidden="true"></i></template></div>
                    <p class="mt-2 whitespace-pre-line break-words text-sm text-ink-700" x-text="body"></p>
                    <p class="mt-4 text-xs text-ink-500">Tu opinión se publicará en la ficha del emprendimiento. Hay un solo voto por persona; puedes retirarlo mientras la votación esté abierta.</p>
                </div>

                {{-- 4. Gracias → embudo de apoyo --}}
                <div x-show="step === 4" x-cloak class="mt-4">
                    <p class="text-ink-700">Tu voto y tu comentario ya cuentan para {{ $venture->name }}. ¿Quieres ayudarles a llegar más lejos?</p>
                    <div class="mt-6 grid gap-2 sm:grid-cols-2">
                        <a href="#apoyar" @click="open = false" class="btn-lion py-3"><x-icon name="hand-holding-heart" /> Apoyar a este equipo</a>
                        <a href="{{ route('investors') }}" class="btn-ghost py-3">Formas de apoyo <x-icon name="arrow-right" /></a>
                    </div>
                    <button type="button" @click="open = false" class="mt-4 w-full text-center text-sm text-ink-500 hover:text-lion-700">Seguir explorando</button>
                </div>

                <p x-show="error" x-text="error" class="mt-4 rounded-xl bg-coral-400/15 px-4 py-2.5 text-sm font-medium text-coral-600"></p>

                <div class="mt-6 flex gap-2" x-show="step < 4">
                    <button type="button" x-show="step > 1" @click="step--; error = ''" class="btn-ghost"><x-icon name="arrow-left" /> Atrás</button>
                    <button type="button" x-show="step < 3" @click="next" class="btn-lion ml-auto">Siguiente <x-icon name="arrow-right" /></button>
                    <button type="button" x-show="step === 3" @click="submit" :disabled="loading" class="btn-lion ml-auto"><x-icon name="heart" /> <span x-text="loading ? 'Enviando…' : 'Dar mi voto'"></span></button>
                </div>
            </div>
        </div>
    </template>

    {{-- Acciones siempre visibles en celular (el pie deja espacio para la barra) --}}
    @push('scripts')<style>@media (max-width: 1023px) { body > footer { padding-bottom: 4.75rem; } }</style>@endpush
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-ink-900/[.06] bg-white/95 p-3 backdrop-blur lg:hidden">
        <div class="mx-auto grid max-w-md grid-cols-2 gap-2">
            <button type="button" @click="start" :disabled="loading" class="btn py-3" :class="voted ? 'border border-lion-300 bg-white text-lion-700' : 'bg-lion-600 text-white'">
                <i class="fa-heart" :class="voted ? 'fa-solid' : 'fa-regular'" aria-hidden="true"></i> <span x-text="voted ? 'Votaste' : 'Votar'"></span>
            </button>
            <a href="#apoyar" class="btn-ghost py-3"><x-icon name="hand-holding-heart" /> Apoyar</a>
        </div>
    </div>
    </div>
</x-layouts.site>
