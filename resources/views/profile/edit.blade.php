<x-layouts.site title="Mi perfil">
    <section class="container-x max-w-3xl pb-10 pt-32 lg:pt-40">
        <a href="{{ route('dashboard') }}" class="text-sm text-white/50 hover:text-white">← Mi actividad</a>
        <h1 class="mt-4 font-display text-4xl font-bold">Mi perfil</h1>

        <div class="mt-8 space-y-6">
            <div class="card-dark p-6 sm:p-8">@include('profile.partials.update-profile-information-form')</div>
            <div class="card-dark p-6 sm:p-8">@include('profile.partials.update-password-form')</div>
            <div class="card-dark border-rose-500/20 p-6 sm:p-8">@include('profile.partials.delete-user-form')</div>
        </div>
    </section>
</x-layouts.site>
