<x-guest-layout>
    <h1 class="font-display text-3xl font-bold sm:text-4xl">Bienvenido de nuevo 👋</h1>
    <p class="mt-2 text-white/60">Ingresa para votar, opinar y apoyar emprendimientos.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <x-input-label for="email" value="Correo electrónico" class="mb-1.5" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="tucorreo@uninavarra.edu.co" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div x-data="{ show: false }">
            <div class="mb-1.5 flex items-center justify-between">
                <x-input-label for="password" value="Contraseña" />
                @if (Route::has('password.request'))
                    <a class="text-sm text-lion-300 hover:text-lion-200" href="{{ route('password.request') }}">¿La olvidaste?</a>
                @endif
            </div>
            <div class="relative">
                <x-text-input id="password" x-bind:type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" class="pr-12" />
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-white/50 hover:text-white" x-text="show ? 'Ocultar' : 'Ver'"></button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-white/20 bg-white/5 text-lion-400 focus:ring-lion-400 focus:ring-offset-ink-950">
            <span class="text-sm text-white/60">Mantener sesión iniciada</span>
        </label>

        <button class="btn-lion w-full py-3.5 text-base">Ingresar</button>
    </form>

    <p class="mt-8 text-center text-sm text-white/60">
        ¿Aún no tienes cuenta? <a href="{{ route('register') }}" class="font-semibold text-lion-300 hover:text-lion-200">Regístrate gratis</a>
    </p>
</x-guest-layout>
