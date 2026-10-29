<x-guest-layout>
    <h1 class="font-display text-4xl font-semibold tracking-tight sm:text-5xl">¡Bienvenida/o!</h1>
    <p class="mt-2 text-white/60">Hola, {{ strtok($user->name, ' ') }}. Crea tu contraseña para entrar como <b>{{ $user->isAdmin() ? 'administrador/a' : 'jurado' }}</b>.</p>

    <form method="POST" action="{{ request()->fullUrl() }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label value="Tu usuario" class="mb-1.5" />
            <x-text-input type="email" :value="$user->email" disabled autocomplete="username" />
        </div>

        <div>
            <x-input-label for="password" value="Nueva contraseña" class="mb-1.5" />
            <x-text-input id="password" type="password" name="password" required autofocus autocomplete="new-password" />
            <p class="mt-1.5 text-xs text-white/60">Mínimo 10 caracteres, con letras y números.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmar contraseña" class="mb-1.5" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
        </div>

        <button class="btn-lion w-full py-3.5 text-base">Guardar y entrar <x-icon name="arrow-right" /></button>
    </form>
</x-guest-layout>
