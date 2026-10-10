<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-lion']) }}>
    {{ $slot }}
</button>
