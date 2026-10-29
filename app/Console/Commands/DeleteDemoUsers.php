<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteDemoUsers extends Command
{
    protected $signature = 'catedra:borrar-usuarios-demo
        {--correo=* : Correo adicional de una cuenta de prueba (se puede repetir)}
        {--listar : Solo muestra todas las cuentas de comunidad e inversionistas, sin borrar}
        {--forzar : No pedir confirmación}';

    protected $description = 'Borra las cuentas de prueba de comunidad e inversionistas (con sus votos, comentarios y apoyos)';

    /** Cuentas que crea el seeder de demostración. */
    private const DEMO = ['/@demo\.co$/', '/^estudiante\d+@uninavarra\.edu\.co$/'];

    public function handle(): int
    {
        $public = User::whereIn('role', ['comunidad', 'inversionista'])->withCount(['votes', 'comments', 'pledges'])->orderBy('email')->get();

        if ($this->option('listar')) {
            $this->table(['Correo', 'Nombre', 'Rol', 'Votos', 'Coment.', 'Apoyos', 'Registro'], $public->map(fn ($u) => [
                $u->email, $u->name, $u->role, $u->votes_count, $u->comments_count, $u->pledges_count, $u->created_at->format('Y-m-d'),
            ]));

            return self::SUCCESS;
        }

        $extra = array_map('strtolower', $this->option('correo'));
        $users = $public->filter(fn ($u) => in_array($u->email, $extra, true)
            || collect(self::DEMO)->contains(fn ($re) => preg_match($re, $u->email)));

        foreach (array_diff($extra, $users->pluck('email')->all()) as $missing) {
            $this->warn("No se encontró una cuenta de comunidad o inversionista con el correo {$missing}.");
        }
        if ($users->isEmpty()) {
            $this->info('No hay cuentas de prueba para borrar.');

            return self::SUCCESS;
        }

        $this->table(['Correo', 'Rol', 'Votos', 'Coment.', 'Apoyos'], $users->map(fn ($u) => [$u->email, $u->role, $u->votes_count, $u->comments_count, $u->pledges_count]));
        if (! $this->option('forzar') && ! $this->confirm("¿Borrar estas {$users->count()} cuentas y todo lo que hicieron? No se puede deshacer.")) {
            return self::SUCCESS;
        }

        $users->each->delete(); // votos, comentarios y apoyos se borran en cascada
        $this->info("Cuentas borradas: {$users->count()}.");

        return self::SUCCESS;
    }
}
