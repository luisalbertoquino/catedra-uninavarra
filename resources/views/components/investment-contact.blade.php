@props(['compact' => false])
{{-- Datos de la persona encargada de la gestión de inversiones (Admin → Configuración) --}}
@php
    use App\Models\Setting;
    $name = Setting::get('investment_contact_name');
    $phone = Setting::get('investment_contact_phone');
    $email = Setting::get('investment_contact_email');
    $wa = Setting::whatsapp('investment_contact_phone');
@endphp
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-lion-400/20 bg-lion-400/[.06] p-5']) }}>
    <p class="text-xs font-semibold uppercase tracking-widest text-lion-300"><x-icon name="handshake" /> Gestión de inversiones</p>
    <p class="mt-2 font-semibold">{{ $name }}</p>
    @unless ($compact)
        <p class="mt-1 text-sm text-white/60">Acompaña a inversionistas y aliados: recibe tu intención de apoyo, te contacta y te conecta con el equipo emprendedor.</p>
    @endunless
    <div class="mt-3 flex flex-wrap gap-2 text-sm">
        @if ($phone)
            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn bg-emerald-500 !px-4 !py-2 text-white hover:bg-emerald-400"><x-icon name="whatsapp" style="brands" /> {{ $phone }}</a>
        @endif
        @if ($email)
            <a href="mailto:{{ $email }}" class="btn-ghost !px-4 !py-2"><x-icon name="envelope" /> {{ $email }}</a>
        @endif
    </div>
</div>
