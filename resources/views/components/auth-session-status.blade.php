@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl bg-emerald-400/10 px-4 py-3 font-medium text-sm text-emerald-300']) }}>
        {{ $status }}
    </div>
@endif
