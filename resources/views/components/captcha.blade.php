@if (config('services.turnstile.site_key'))
    {{-- CAPTCHA de Cloudflare Turnstile (solo si hay llaves configuradas en .env) --}}
    <div class="cf-turnstile mt-2" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="dark" data-language="es"></div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
