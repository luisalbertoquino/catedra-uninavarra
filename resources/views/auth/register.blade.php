<x-guest-layout>
    <h1 class="font-display text-4xl font-semibold tracking-tight sm:text-5xl">Únete a la comunidad</h1>
    <p class="mt-2 text-white/60">Crea tu cuenta en menos de un minuto.</p>

    <x-google-button class="mt-8" label="Registrarme con Google" :perfil="$role === 'inversionista' ? 'inversionista' : null" />

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5" x-data="{ role: @js(old('role', $role)) }">
        @csrf
        <x-honeypot />

        <fieldset>
            <legend class="mb-2 text-sm font-medium text-white/80">¿Cómo quieres participar?</legend>
            <div class="grid grid-cols-2 gap-3">
                @foreach (['comunidad' => ['check-to-slot', 'Comunidad', 'Votar y opinar'], 'inversionista' => ['briefcase', 'Inversionista / Aliado', 'Invertir, donar o mentorear']] as $key => [$icon, $label, $hint])
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="{{ $key }}" x-model="role" class="peer sr-only">
                        <span class="relative flex h-full flex-col rounded-2xl border-2 border-white/10 bg-white/[.03] p-4 transition peer-checked:!border-lion-600 peer-checked:!bg-lavanda/60 peer-focus-visible:ring-2 peer-focus-visible:ring-lion-400 hover:border-white/30">
                            <span class="absolute right-3 top-3 grid h-6 w-6 place-items-center rounded-full border-2 text-xs transition" :class="role === @js($key) ? 'border-lion-600 bg-lion-600' : 'border-lion-300 bg-white'"><x-icon name="check" style="solid" x-show="role === @js($key)" x-bind:style="'color:#fff'" /></span>
                            <x-icon :name="$icon" class="text-2xl" />
                            <span class="mt-2 text-sm font-semibold leading-tight">{{ $label }}</span>
                            <span class="mt-0.5 text-xs text-white/50">{{ $hint }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </fieldset>

        <div>
            <x-input-label for="name" value="Nombre completo" class="mb-1.5" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" class="mb-1.5" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <p class="mt-1.5 text-xs text-white/45" x-show="role === 'comunidad'">Si eres Navarrista, usa tu correo institucional.</p>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div x-show="role === 'inversionista'" x-collapse x-cloak class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="organization" value="Empresa u organización" class="mb-1.5" />
                <x-text-input id="organization" type="text" name="organization" :value="old('organization')" />
            </div>
            <div>
                <x-input-label for="phone" value="Teléfono / WhatsApp (obligatorio)" class="mb-1.5" />
                <x-text-input id="phone" type="tel" name="phone" :value="old('phone')" x-bind:required="role === 'inversionista'" autocomplete="tel" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <div x-show="role === 'comunidad'" x-collapse>
            <x-input-label for="academic_program" value="Programa académico (opcional)" class="mb-1.5" />
            <x-text-input id="academic_program" type="text" name="academic_program" :value="old('academic_program')" placeholder="Ej. Ingeniería de Sistemas" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="password" value="Contraseña" class="mb-1.5" />
                <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password_confirmation" value="Confirmar contraseña" class="mb-1.5" />
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>
        </div>

        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 border-lion-200 bg-lavanda/30 p-4 transition has-[:checked]:border-lion-600 has-[:checked]:bg-lavanda/70">
            <input type="checkbox" name="terms" value="1" @checked(old('terms')) required class="mt-0.5 text-lion-600 focus:ring-lion-500">
            <span class="text-sm leading-relaxed text-ink-700">Autorizo el tratamiento de mis datos personales conforme a la Ley 1581 de 2012 y la <a href="{{ route('privacy') }}" target="_blank" class="font-semibold text-lion-700 underline">política de privacidad</a> de UNINAVARRA, para fines de la Cátedra y contacto con los emprendimientos.</span>
        </label>
        <x-input-error :messages="$errors->get('terms')" />

        <x-captcha />
        <button class="btn-lion w-full py-3.5 text-base">Crear cuenta</button>
    </form>

    <p class="mt-8 text-center text-sm text-white/60">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="font-semibold text-lion-300 hover:text-lion-200">Ingresa aquí</a>
    </p>
</x-guest-layout>
