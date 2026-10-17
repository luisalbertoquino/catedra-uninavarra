<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Evaluation;
use App\Models\RubricCriterion;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Datos de DEMOSTRACIÓN del módulo de jurados. Es idempotente: se puede ejecutar sobre una base
 * existente sin borrar nada (php artisan db:seed --class=JuryDemoSeeder).
 * Jurados: jurado1..4@uninavarra.edu.co y experto@uninavarra.edu.co, contraseña "password".
 */
class JuryDemoSeeder extends Seeder
{
    /** Videos públicos de la Cátedra usados como ejemplo mientras los equipos cargan su pitch */
    private const DEMO_VIDEOS = [
        'https://www.youtube.com/watch?v=0r4d-hZam6I',
        'https://www.youtube.com/watch?v=bRAVKiJf0IY',
    ];

    public function run(): void
    {
        // 1) Rúbrica de la Cátedra en cada edición que no tenga
        Edition::doesntHave('criteria')->get()->each(function (Edition $e) {
            foreach (RubricCriterion::DEFAULTS as $i => [$name, $description]) {
                $e->criteria()->create(['name' => $name, 'description' => $description, 'weight' => 20, 'max_score' => 5, 'sort' => $i]);
            }
        });

        // 2) Jurados
        $juries = collect([
            ['Profesor Experto (demo)', 'experto@uninavarra.edu.co', true, 'Docente investigador'],
            ['Jurado Uno (demo)', 'jurado1@uninavarra.edu.co', false, 'Empresario invitado'],
            ['Jurado Dos (demo)', 'jurado2@uninavarra.edu.co', false, 'Mentor Fondo Emprender'],
            ['Jurado Tres (demo)', 'jurado3@uninavarra.edu.co', false, 'Docente UNINAVARRA'],
            ['Jurado Cuatro (demo)', 'jurado4@uninavarra.edu.co', false, 'Inversionista ángel'],
        ])->map(fn ($j) => User::firstOrCreate(['email' => $j[1]], [
            'name' => $j[0], 'role' => 'jurado', 'is_expert' => $j[2], 'organization' => $j[3],
            'password' => Hash::make('password'), 'email_verified_at' => now(),
        ]));

        $active = Edition::where('is_active', true)->with('criteria')->first();
        if (! $active) {
            return;
        }

        // 3) Videos de ejemplo para los emprendimientos de la edición activa que no tengan
        $active->ventures()->whereNull('video_url')->where('cover', 'like', 'https://picsum.photos/%')->orderBy('id')->get() // solo los ficticios
            ->each(fn ($v, $i) => $v->update(['video_url' => self::DEMO_VIDEOS[$i % count(self::DEMO_VIDEOS)]]));

        // 4) Evaluaciones parciales de demo (solo sobre emprendimientos ficticios)
        $ventures = $active->ventures()->published()->where('cover', 'like', 'https://picsum.photos/%')->orderBy('id')->get();
        foreach ($juries as $j => $jury) {
            if ($jury->evaluations()->where('edition_id', $active->id)->exists()) {
                continue;
            }
            $howMany = (int) round($ventures->count() * [0.5, 0.9, 0.7, 0.6, 0.4][$j % 5]);
            foreach ($ventures->take($howMany) as $k => $venture) {
                $base = 5 - intdiv($k, 4); // los primeros emprendimientos (más votados en la demo) puntúan mejor
                $scores = $active->criteria->mapWithKeys(fn ($c) => [$c->id => max(1, min($c->max_score, $base + mt_rand(-1, 0)))])->all();
                $evaluation = Evaluation::create([
                    'edition_id' => $active->id, 'venture_id' => $venture->id, 'user_id' => $jury->id,
                    'total_score' => Evaluation::computeTotal($active->criteria, $scores),
                    'comment' => mt_rand(0, 1) ? collect([
                        'Propuesta sólida; validar mejor el precio con clientes reales.',
                        'Buen pitch. Falta claridad en el modelo de ingresos.',
                        'Alto impacto regional, recomiendo buscar aliados en la Gobernación.',
                        'El equipo muestra dominio técnico. Trabajar la proyección financiera.',
                    ])->random() : null,
                ]);
                foreach ($scores as $criterionId => $score) {
                    $evaluation->scores()->create(['rubric_criterion_id' => $criterionId, 'score' => $score]);
                }
            }
        }

        // 5) Un voto por persona: en la demo se conserva solo el primer voto de cada usuario por edición
        Vote::query()->whereNotIn('id', Vote::query()->selectRaw('MIN(id)')->groupBy('user_id', 'edition_id'))
            ->get()->each->delete();

        // 6) Inversionistas que aceptan aparecer en el ranking público
        User::where('role', 'inversionista')->whereHas('pledges')->orderBy('id')->take(6)->update(['show_in_investor_ranking' => true]);
    }
}
