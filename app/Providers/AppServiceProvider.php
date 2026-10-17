<?php

namespace App\Providers;

use App\Models\Edition;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Contraseñas: mínimo 8 caracteres con letras y números
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        // Edición vigente disponible en el layout público (anuncio de votación)
        View::composer('components.layouts.site', function ($view) {
            static $edition = false;
            if ($edition === false) {
                $edition = Edition::current();
            }
            $view->with('siteEdition', $edition);
        });
    }
}
