<x-layouts.site title="Inversionistas" description="Conoce a los inversionistas y empresarios invitados al LION HUB EVENT de Cátedra UNINAVARRA.">
    <section class="ecos-soft-violeta ecos-band pb-12 pt-32 lg:pt-36">
        <div class="container-x">
            <span class="eyebrow"><x-icon name="people-group" /> LION HUB EVENT</span>
            <h1 class="mt-3 page-title">Inversionistas</h1>
            <p class="mt-4 max-w-2xl text-lg text-white/60">Empresarios, consultores y expertos en inversión que acompañan a los equipos Navarristas, escuchan sus pitch y pueden impulsar sus ideas.</p>
        </div>
    </section>

    <section class="container-x pt-12">
        @if ($profiles->isEmpty())
            <p class="py-16 text-center text-white/50">Muy pronto conocerás a los inversionistas de esta edición.</p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($profiles as $p)
                    <x-investor-card :profile="$p" id="perfil-{{ $p->id }}" class="scroll-mt-32" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Embudo: de conocer a los inversionistas a apoyar --}}
    <section class="container-x pt-16">
        <div class="tarjeta-ecos reveal">
            <span class="tarjeta-ecos__tab"><x-icon name="hand-holding-heart" /> ¿Quieres sumarte?</span>
            <div class="tarjeta-ecos__body flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center">
                <div>
                    <h2 class="font-display text-3xl font-medium text-lion-700">Tú también puedes apoyar a un emprendimiento</h2>
                    <p class="mt-2 max-w-2xl text-ink-700">Con inversión, donación, asesorías, contactos, equipos o mercadeo. Registras tu intención y la Cátedra te conecta con el equipo.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('investors') }}" class="btn-lion px-6 py-3">Quiero apoyar <x-icon name="arrow-right" /></a>
                    <a href="{{ route('portfolios') }}" class="btn-ghost bg-white px-6 py-3"><x-icon name="book-open" /> Ver portafolios</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.site>
