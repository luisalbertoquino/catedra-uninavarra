@props(['compact' => false])
{{-- Formas de apoyo que puede ofrecer un inversionista o aliado --}}
<div {{ $attributes->merge(['class' => 'grid gap-3 '.($compact ? 'grid-cols-2 sm:grid-cols-3' : 'sm:grid-cols-2 lg:grid-cols-4')]) }}>
    @foreach (\App\Models\SupportPledge::TYPES as $t)
        <div class="flex items-center gap-3 rounded-ecos bg-tarjeta p-3.5 sm:p-4">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white"><x-icon :name="$t['icon']" /></span>
            <span class="min-w-0">
                <span class="block break-words font-semibold leading-tight text-lion-700">{{ $t['label'] }}</span>
                @unless ($compact)<span class="mt-0.5 block text-sm leading-snug text-ink-600">{{ $t['hint'] }}</span>@endunless
            </span>
        </div>
    @endforeach
</div>
