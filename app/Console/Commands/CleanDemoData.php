<?php

namespace App\Console\Commands;

use App\Models\Edition;
use App\Models\Venture;
use Illuminate\Console\Command;

class CleanDemoData extends Command
{
    protected $signature = 'catedra:limpiar-demo {--edicion= : Slug de la edición (por defecto la activa)} {--forzar : No pedir confirmación}';

    protected $description = 'Elimina los emprendimientos ficticios del seeder (y sus votos, comentarios, apoyos y evaluaciones) de una edición';

    public function handle(): int
    {
        $edition = $this->option('edicion') ? Edition::where('slug', $this->option('edicion'))->first() : Edition::current();
        if (! $edition) {
            $this->error('No se encontró la edición.');

            return self::FAILURE;
        }

        // Los emprendimientos de demostración se reconocen por su portada de picsum.photos
        $demo = $edition->ventures()->where('cover', 'like', 'https://picsum.photos/%')->get();
        if ($demo->isEmpty()) {
            $this->info("No hay emprendimientos de demostración en {$edition->name}.");

            return self::SUCCESS;
        }

        $this->line($demo->pluck('name')->join(', '));
        if (! $this->option('forzar') && ! $this->confirm("¿Eliminar estos {$demo->count()} emprendimientos ficticios de {$edition->name}?")) {
            return self::SUCCESS;
        }

        $demo->each(fn (Venture $v) => $v->delete()); // votos, comentarios, apoyos y evaluaciones se borran en cascada
        $this->info("Eliminados {$demo->count()} emprendimientos de demostración.");

        return self::SUCCESS;
    }
}
