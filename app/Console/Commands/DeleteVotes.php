<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\Edition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteVotes extends Command
{
    protected $signature = 'catedra:borrar-votos
        {--edicion= : Slug de la edición (por defecto la activa)}
        {--con-comentarios : También borra los comentarios y estrellas de esa edición}
        {--forzar : No pedir confirmación}';

    protected $description = 'Borra los votos (de prueba) de una edición; los usuarios recuperan su voto';

    public function handle(): int
    {
        $edition = $this->option('edicion') ? Edition::where('slug', $this->option('edicion'))->first() : Edition::current();
        if (! $edition) {
            $this->error('No se encontró la edición.');

            return self::FAILURE;
        }

        $votes = $edition->votes();
        $comments = Comment::whereIn('venture_id', $edition->ventures()->select('id'));
        $withComments = $this->option('con-comentarios');

        $this->line("Edición: {$edition->name}");
        $this->line('Votos: '.$votes->count().($withComments ? ' · Comentarios: '.$comments->count() : ''));
        if (! $this->option('forzar') && ! $this->confirm('¿Borrarlos? No se puede deshacer.')) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($votes, $comments, $withComments) {
            $votes->delete();
            if ($withComments) {
                $comments->delete();
            }
        });
        $this->info('Listo. Cada usuario puede volver a votar.');

        return self::SUCCESS;
    }
}
