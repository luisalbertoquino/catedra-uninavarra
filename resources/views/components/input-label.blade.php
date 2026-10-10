@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-white/80']) }}>
    {{ $value ?? $slot }}
</label>
