@props(['compact' => false])
{{-- Datos de la persona encargada de la gestión de inversiones (Admin → Configuración) --}}
@php
    use App\Models\Setting;
    $name = Setting::get('investment_contact_name');
    $phone = Setting::get('investment_contact_phone');
    $email = Setting::get('investment_contact_email');
    $wa = Setting::whatsapp('investment_contact_phone');
@endphp
<div {{ $attributes->merge(['class' => 'rounded-ecos border border-lion-200 bg-white p-5']) }}>
    <p class="text-xs font-semibold uppercase tracking-widest text-lion-300"><x-icon name="handshake" /> Gestión de inversiones</p>
    <p class="mt-2 font-semibold">{{ $name }}</p>
    @unless ($compact)
        <p class="mt-1 text-sm text-white/60">Acompaña a inversionistas y aliados: recibe tu intención de apoyo, te contacta y te conecta con el equipo emprendedor.</p>
    @endunless
    <div class="mt-3 flex flex-wrap gap-2 text-sm">
        @if ($phone)
            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn bg-emerald-700 !px-4 !py-2 text-white hover:bg-emerald-600"><x-icon name="whatsapp" style="brands" /> {{ $phone }}</a>
        @endif
        @if ($email)
            <a href="mailto:{{ $email }}" class="btn-ghost min-w-0 max-w-full !px-4 !py-2"><x-icon name="envelope" /> <span class="break-all">{{ $email }}</span></a>
        @endif
    </div>
</div>
