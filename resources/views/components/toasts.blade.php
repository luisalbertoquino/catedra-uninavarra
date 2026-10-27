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
             class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-ecos border border-lion-200 bg-white px-4 py-3 text-sm text-ink-800 shadow-2xl shadow-lion-900/15">
            <i class="ico fa-solid mt-0.5 shrink-0 text-base" aria-hidden="true"
               :class="item.type === 'success' ? 'fa-circle-check' : (item.type === 'warning' ? 'fa-circle-exclamation' : 'fa-circle-info')"></i>
            <p class="flex-1 leading-snug" x-text="item.message"></p>
            <button type="button" class="text-ink-400 hover:text-lion-700" @click="items = items.filter(i => i.id !== item.id)" aria-label="Cerrar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
    </template>
</div>
