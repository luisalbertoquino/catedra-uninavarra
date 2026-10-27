@props(['status'])
@php
    $styles = [
        'pendiente' => 'bg-lion-100 text-lion-800',
        'contactado' => 'bg-sky-100 text-sky-800',
        'en_proceso' => 'bg-violet-100 text-violet-800',
        'concretado' => 'bg-emerald-100 text-emerald-800',
        'descartado' => 'bg-slate-100 text-slate-600',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'a-badge '.($styles[$status] ?? 'bg-slate-100 text-slate-600')]) }}>{{ \App\Models\SupportPledge::STATUSES[$status] ?? $status }}</span>
