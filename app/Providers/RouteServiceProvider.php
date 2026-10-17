<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/mi-cuenta';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Tope general por IP para todo el sitio: frena scrapers y ráfagas que saturen el servidor
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute((int) config('catedra.rate_limit_per_minute', 180))
                ->by($request->ip())
                ->response(fn () => response()->view('errors.429', [], 429));
        });

        // Registro y recuperación de contraseña: pocas veces por IP para evitar creación masiva de cuentas
        RateLimiter::for('register', function (Request $request) {
            return [
                Limit::perMinute(3)->by('min:'.$request->ip()),
                Limit::perDay(10)->by('day:'.$request->ip()),
            ];
        });
    }
}
