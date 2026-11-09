@props(['edition', 'winners' => null])
{{-- Premios de la edición por puesto. Con $winners (emprendimientos por posición) muestra quién ganó cada uno. --}}
<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
    @foreach ($edition->prizePlaces() as $place)
        @php $winner = $winners?->get($place['position']); @endphp
        <article class="card-dark reveal flex flex-col p-6">
            <div class="flex items-center gap-2 font-display text-xl font-medium text-lion-700">
                <x-icon :name="$place['position'] === 1 ? 'trophy' : ($place['position'] === 2 ? 'medal' : 'award')" /> {{ $place['label'] }}
            </div>
            @if ($winner)
                <a href="{{ route('ventures.show', $winner) }}" class="mt-1 font-semibold text-ink-900 hover:underline">{{ $winner->name }}</a>
            @endif
            <ul class="mt-4 space-y-4">
                @foreach ($place['items'] as $item)
                    <li>
                        <div class="font-semibold text-ink-900">{{ $item['title'] }}</div>
                        @if (! empty($item['amount']))
                            <div class="font-display text-xl font-semibold text-lion-700">{{ \App\Models\Edition::money($item['amount']) }}</div>
                        @endif
                        @if (! empty($item['details']))
                            <details class="mt-1 text-sm text-ink-600">
                                <summary class="cursor-pointer font-medium text-ink-700">Ver condiciones</summary>
                                <p class="mt-2">{{ $item['details'] }}</p>
                            </details>
                        @endif
                    </li>
                @endforeach
            </ul>
        </article>
    @endforeach
</div>
