<x-layouts.jury :title="'Evaluar '.$venture->name">
    @php
        $open = $edition->isJuryOpen();
        $criteria = $edition->criteria->map(fn ($c) => ['id' => $c->id, 'weight' => $c->weight, 'max' => $c->max_score])->values();
        $initial = $edition->criteria->mapWithKeys(fn ($c) => [$c->id => (int) old("scores.{$c->id}", $scores[$c->id] ?? 0)]);
        $isExpert = auth()->user()->is_expert;
    @endphp

    <section class="container-x pt-8">
        {{-- Navegación --}}
        <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
            <a href="{{ route('jury.index') }}" class="inline-flex items-center gap-2 text-white/60 hover:text-white"><x-icon name="arrow-left" /> Todos los emprendimientos</a>
            <div class="flex items-center gap-2">
                <span class="text-white/50">{{ $pos + 1 }} de {{ $ids->count() }}</span>
                @if ($prev)<a href="{{ route('jury.show', $prev) }}" class="grid h-9 w-9 place-items-center rounded-lg border border-white/15 hover:bg-white/5" title="Anterior: {{ $prev->name }}"><x-icon name="chevron-left" /></a>@endif
                @if ($next)<a href="{{ route('jury.show', $next) }}" class="grid h-9 w-9 place-items-center rounded-lg border border-white/15 hover:bg-white/5" title="Siguiente: {{ $next->name }}"><x-icon name="chevron-right" /></a>@endif
            </div>
        </div>

        <div class="mt-6 grid gap-8 lg:grid-cols-12">
            {{-- ============ Video e información ============ --}}
            <div class="space-y-8 lg:col-span-7">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($venture->category)<span class="chip">@if ($venture->category->icon)<x-icon :name="$venture->category->icon" />@endif {{ $venture->category->name }}</span>@endif
                        @if ($venture->academic_program)<span class="chip"><x-icon name="graduation-cap" /> {{ $venture->academic_program }}</span>@endif
                        @if ($evaluation)<span class="chip border-lion-400/40 text-lion-100"><x-icon name="circle-check" /> Evaluado</span>@endif
                    </div>
                    <h1 class="mt-3 font-display text-4xl font-semibold tracking-tight sm:text-5xl">{{ $venture->name }}</h1>
                    @if ($venture->tagline)<p class="mt-2 text-lg text-white/65">{{ $venture->tagline }}</p>@endif
                </div>

                @if ($venture->video_embed_url)
                    <div class="relative aspect-video overflow-hidden rounded-2xl border border-white/10 bg-black shadow-2xl">
                        <iframe src="{{ $venture->video_embed_url }}" title="Video pitch {{ $venture->name }}" class="absolute inset-0 h-full w-full"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                    <a href="{{ $venture->video_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm text-white/50 hover:text-white"><x-icon name="youtube" style="brands" /> Abrir el video en YouTube</a>
                @elseif ($venture->video_url)
                    <a href="{{ $venture->video_url }}" target="_blank" rel="noopener" class="card-dark card-dark-hover flex items-center gap-4 p-6">
                        <span class="grid h-14 w-14 place-items-center rounded-full bg-lion-400/10"><x-icon name="play" class="text-xl" /></span>
                        <span><span class="block font-semibold">Ver video pitch</span><span class="text-sm text-white/50">Se abrirá en una nueva pestaña</span></span>
                    </a>
                @else
                    <div class="card-dark grid aspect-video place-items-center p-8 text-center">
                        <div>
                            <x-icon name="video-slash" class="text-4xl" />
                            <p class="mt-3 font-semibold">Este equipo aún no tiene video cargado</p>
                            <p class="mt-1 text-sm text-white/50">Puedes evaluar con la información escrita o esperar a que el administrador cargue el video.</p>
                        </div>
                    </div>
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ([['bullseye', 'El problema', $venture->problem], ['lightbulb', 'La solución', $venture->solution], ['users-viewfinder', 'Mercado objetivo', $venture->target_market]] as [$icon, $label, $text])
                        @if ($text)
                            <div @class(['card-dark p-6', 'md:col-span-2' => $loop->last])>
                                <h3 class="flex items-center gap-2 font-display text-lg font-semibold"><x-icon :name="$icon" /> {{ $label }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-white/65">{!! nl2br(e($text)) !!}</p>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if ($venture->description)
                    <div>
                        <h3 class="font-display text-lg font-semibold">Descripción</h3>
                        <p class="mt-2 leading-relaxed text-white/65">{!! nl2br(e($venture->description)) !!}</p>
                    </div>
                @endif

                @if ($venture->members->isNotEmpty())
                    <div>
                        <h3 class="font-display text-lg font-semibold">Equipo</h3>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($venture->members as $m)
                                <div class="glass flex items-center gap-3 rounded-xl p-3">
                                    <x-avatar :name="$m->name" :src="$m->photo_url" size="sm" />
                                    <div class="min-w-0 text-sm">
                                        <div class="truncate font-semibold">{{ $m->name }}</div>
                                        <div class="truncate text-xs text-white/50">{{ $m->role }}@if ($m->academic_program) · {{ $m->academic_program }}@endif</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- ============ Rúbrica ============ --}}
            <aside class="lg:col-span-5">
                <form method="POST" action="{{ route('jury.store', $venture) }}" class="card-dark p-6 lg:sticky lg:top-28"
                      x-data="{
                          criteria: @js($criteria),
                          scores: @js($initial),
                          get complete() { return this.criteria.every(c => this.scores[c.id] > 0) },
                          get total() {
                              const w = this.criteria.reduce((a, c) => a + c.weight, 0) || 1;
                              const s = this.criteria.reduce((a, c) => a + ((this.scores[c.id] || 0) / c.max) * c.weight, 0);
                              return (s / w * 100).toFixed(1).replace('.', ',');
                          }
                      }">
                    @csrf
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-display text-2xl font-semibold">Rúbrica</h2>
                            <p class="text-sm text-white/50">Califica cada criterio de 1 a su máximo.</p>
                        </div>
                        <div class="text-right">
                            <div class="countdown-num font-display text-4xl font-semibold leading-none tabular-nums" x-text="total">0</div>
                            <div class="mt-1 text-xs text-white/50">de 100 puntos</div>
                        </div>
                    </div>

                    @if ($isExpert)
                        <p class="mt-4 rounded-lg border border-lion-400/30 bg-lion-400/10 px-3 py-2 text-sm text-lion-100">
                            <x-icon name="award" /> Evalúas como <b>{{ $edition->expert_title }}</b>: tu puntaje pesa ×{{ rtrim(rtrim(number_format($edition->expert_weight, 2, ',', '.'), '0'), ',') }} en el comité.
                        </p>
                    @endif

                    @if ($edition->criteria->isEmpty())
                        <p class="mt-6 rounded-lg bg-white/5 p-4 text-sm text-white/60">La edición aún no tiene rúbrica configurada. El administrador debe definirla en la edición.</p>
                    @else
                        <div class="mt-6 space-y-5">
                            @foreach ($edition->criteria as $c)
                                <fieldset>
                                    <div class="flex items-baseline justify-between gap-3">
                                        <legend class="font-semibold">{{ $c->name }}</legend>
                                        <span class="shrink-0 text-xs text-white/40">{{ $c->weight }}%</span>
                                    </div>
                                    @if ($c->description)<p class="mt-0.5 text-xs text-white/50">{{ $c->description }}</p>@endif
                                    <div class="mt-2 grid gap-1.5" style="grid-template-columns: repeat({{ $c->max_score }}, minmax(0, 1fr))">
                                        @for ($n = 1; $n <= $c->max_score; $n++)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="scores[{{ $c->id }}]" value="{{ $n }}" class="peer sr-only" x-model.number="scores[{{ $c->id }}]" @disabled(! $open)>
                                                <span class="grid h-10 place-items-center rounded-lg border border-white/10 bg-white/[.03] text-sm font-semibold text-white/70 transition peer-checked:border-lion-400 peer-checked:bg-lion-400 peer-checked:text-ink-950 peer-focus-visible:ring-2 peer-focus-visible:ring-lion-400 hover:border-white/30">{{ $n }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                    <x-input-error :messages="$errors->get('scores.'.$c->id)" class="mt-1" />
                                </fieldset>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            <label for="comment" class="label-dark">Retroalimentación para el equipo <span class="text-white/40">· opcional</span></label>
                            <textarea id="comment" name="comment" rows="4" maxlength="3000" class="input-dark" @disabled(! $open)
                                      placeholder="Fortalezas, aspectos por mejorar, recomendaciones…">{{ old('comment', $evaluation?->comment) }}</textarea>
                        </div>

                        @if ($open)
                            <input type="hidden" name="next_id" value="{{ $nextPending?->id }}">
                            <div class="mt-6 flex flex-col gap-2 sm:flex-row">
                                <button name="after" value="quedarse" class="btn-ghost flex-1" :disabled="!complete"><x-icon name="floppy-disk" style="regular" /> Guardar</button>
                                @if ($nextPending)
                                    <button name="after" value="siguiente" class="btn-lion flex-1" :disabled="!complete">Guardar y siguiente <x-icon name="arrow-right" /></button>
                                @endif
                            </div>
                            <p x-show="!complete" class="mt-2 text-center text-xs text-white/40">Califica todos los criterios para guardar.</p>
                            @if ($evaluation)
                                <p class="mt-3 text-center text-xs text-white/40">Última actualización: {{ $evaluation->updated_at->translatedFormat('j M Y, g:i a') }}</p>
                            @endif
                        @else
                            <p class="mt-6 rounded-lg bg-white/5 p-3 text-center text-sm text-white/60"><x-icon name="lock" /> La evaluación de esta edición está cerrada.</p>
                        @endif
                    @endif
                </form>
            </aside>
        </div>
    </section>
</x-layouts.jury>
