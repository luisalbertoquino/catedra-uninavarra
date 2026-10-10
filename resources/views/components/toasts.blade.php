{{-- Notificaciones flotantes: escucha el evento "toast" y muestra los mensajes flash de sesión --}}
<div x-data="{
        items: [],
        add(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type });
            setTimeout(() => this.items = this.items.filter(i => i.id !== id), 4500);
        }
    }"
    x-init="
        @if (session('status')) add(@js(session('status')), 'success'); @endif
        @if (session('error')) add(@js(session('error')), 'warning'); @endif
    "
    @toast.window="add($event.detail.message, $event.detail.type)"
    class="pointer-events-none fixed inset-x-0 bottom-4 z-[90] flex flex-col items-center gap-2 px-4 sm:bottom-6 sm:items-end sm:px-6"
    aria-live="polite">
    <template x-for="item in items" :key="item.id">
        <div x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition duration-200" x-transition:leave-end="opacity-0"
             class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border border-white/10 bg-ink-900/95 px-4 py-3 text-sm text-white shadow-2xl shadow-black/40 backdrop-blur">
            <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs font-bold"
                  :class="{ 'bg-emerald-400 text-ink-950': item.type === 'success', 'bg-lion-400 text-ink-950': item.type === 'warning', 'bg-aqua-400 text-ink-950': item.type === 'info' }"
                  x-text="item.type === 'success' ? '✓' : (item.type === 'warning' ? '!' : 'i')"></span>
            <p class="flex-1 leading-snug" x-text="item.message"></p>
            <button type="button" class="text-white/50 hover:text-white" @click="items = items.filter(i => i.id !== item.id)" aria-label="Cerrar">✕</button>
        </div>
    </template>
</div>
