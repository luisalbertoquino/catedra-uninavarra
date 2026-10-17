<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-white/60">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-white/80">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-white/60 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lion-400">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-emerald-300">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <x-input-label for="phone" value="Teléfono" />
                <x-text-input id="phone" name="phone" type="tel" class="mt-1" :value="old('phone', $user->phone)" />
            </div>
            <div>
                <x-input-label for="organization" value="Empresa u organización" />
                <x-text-input id="organization" name="organization" type="text" class="mt-1" :value="old('organization', $user->organization)" />
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="academic_program" value="Programa académico (si eres Navarrista)" />
                <x-text-input id="academic_program" name="academic_program" type="text" class="mt-1" :value="old('academic_program', $user->academic_program)" />
            </div>
            <label class="flex items-start gap-3 text-sm text-white/70 sm:col-span-2">
                <input type="hidden" name="show_in_investor_ranking" value="0">
                <input type="checkbox" name="show_in_investor_ranking" value="1" @checked(old('show_in_investor_ranking', $user->show_in_investor_ranking))
                       class="mt-0.5 rounded border-white/20 bg-white/5 text-lion-400 focus:ring-lion-400 focus:ring-offset-ink-950">
                <span>Aparecer en el ranking público de inversionistas con mi nombre u organización.</span>
            </label>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-white/60"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
