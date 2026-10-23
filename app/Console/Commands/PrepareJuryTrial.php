<?php

namespace App\Console\Commands;

use App\Models\Edition;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Prepara la prueba del comité técnico en la edición activa:
 *  - crea las cuentas de los dos jurados y del Profesor experto (contraseña aleatoria, se muestra una sola vez),
 *  - pone un video de ejemplo a los emprendimientos que aún no tienen video, para practicar la evaluación.
 *
 *   php artisan catedra:jurados-prueba                 crear cuentas y poner el video de ejemplo
 *   php artisan catedra:jurados-prueba --quitar-demo   además elimina los jurados de demostración (contraseña "password")
 *   php artisan catedra:jurados-prueba --limpiar       al terminar la práctica: quita el video de ejemplo y las evaluaciones hechas sobre él
 */
class PrepareJuryTrial extends Command
{
    public const VIDEO = 'https://www.youtube.com/watch?v=me334IBqnQI';

    /** Cuentas del comité técnico (correo, nombre, experto) — el admin puede cambiarlas en Admin → Jurados */
    public const ACCOUNTS = [
        ['jurado.tn@uninavarra.edu.co', 'Jurado TN', false],
        ['jurado.vice@uninavarra.edu.co', 'Jurado VICE', false],
        ['profesor.experto@uninavarra.edu.co', 'Profesor experto', true],
    ];

    /** Jurados creados por JuryDemoSeeder */
    public const DEMO_EMAILS = ['experto@uninavarra.edu.co', 'jurado1@uninavarra.edu.co', 'jurado2@uninavarra.edu.co', 'jurado3@uninavarra.edu.co', 'jurado4@uninavarra.edu.co'];

    protected $signature = 'catedra:jurados-prueba
        {--video= : Enlace del video de ejemplo (por defecto el de la Cátedra)}
        {--nuevas-claves : Genera contraseñas nuevas también para las cuentas que ya existen}
        {--quitar-demo : Elimina los jurados de demostración y sus evaluaciones}
        {--limpiar : Quita el video de ejemplo y las evaluaciones de práctica}
        {--forzar : No pedir confirmación}';

    protected $description = 'Crea las cuentas de los jurados y del Profesor experto y pone un video de ejemplo para practicar';

    public function handle(): int
    {
        $edition = Edition::current();
        if (! $edition) {
            $this->error('No hay una edición activa.');

            return self::FAILURE;
        }
        $video = $this->option('video') ?: self::VIDEO;

        if ($this->option('limpiar')) {
            return $this->clean($edition, $video);
        }

        if ($this->option('quitar-demo')) {
            $demo = User::whereIn('email', self::DEMO_EMAILS)->where('role', 'jurado')->get();
            if ($demo->isNotEmpty() && ($this->option('forzar') || $this->confirm("Se eliminarán {$demo->count()} jurados de demostración y sus evaluaciones. ¿Continuar?", true))) {
                DB::transaction(fn () => $demo->each(function ($u) {
                    $u->evaluations()->each(fn ($e) => $e->delete());
                    $u->delete();
                }));
                $this->info("Jurados de demostración eliminados: {$demo->count()}");
            }
        }

        $rows = [];
        foreach (self::ACCOUNTS as [$email, $name, $expert]) {
            $user = User::firstWhere('email', $email);
            $password = null;
            if (! $user || $this->option('nuevas-claves')) {
                $password = Str::password(12, symbols: false);
            }
            $user ??= new User(['email' => $email, 'name' => $name]);
            $user->fill(['role' => 'jurado', 'is_expert' => $expert]);
            if ($password) {
                $user->password = Hash::make($password);
            }
            $user->email_verified_at ??= now();
            $user->save();
            $rows[] = [$user->name, $email, $expert ? $edition->expert_title : 'Jurado', $password ?? '(sin cambios)'];
        }

        $assigned = $edition->ventures()->published()->where(fn ($q) => $q->whereNull('video_url')->orWhere('video_url', ''))->update(['video_url' => $video]);

        $this->table(['Nombre', 'Correo', 'Rol', 'Contraseña'], $rows);
        $this->warn('Copia las contraseñas ahora: no se vuelven a mostrar. Se cambian en Admin → Jurados.');
        $this->info("{$edition->name}: video de ejemplo asignado a {$assigned} emprendimientos sin video.");
        $this->line('Al terminar la práctica: php artisan catedra:jurados-prueba --limpiar');

        return self::SUCCESS;
    }

    private function clean(Edition $edition, string $video): int
    {
        $ventures = $edition->ventures()->where('video_url', $video)->pluck('id');
        $evaluations = Evaluation::where('edition_id', $edition->id)->whereIn('venture_id', $ventures)->get();

        if (! $this->option('forzar') && ! $this->confirm("Se quitará el video de ejemplo de {$ventures->count()} emprendimientos y se borrarán {$evaluations->count()} evaluaciones de práctica. ¿Continuar?", true)) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($edition, $ventures, $evaluations) {
            $evaluations->each(fn ($e) => $e->delete());
            $edition->ventures()->whereIn('id', $ventures)->update(['video_url' => null]);
        });
        $this->info("Listo: {$ventures->count()} videos de ejemplo quitados y {$evaluations->count()} evaluaciones de práctica borradas.");

        return self::SUCCESS;
    }
}
