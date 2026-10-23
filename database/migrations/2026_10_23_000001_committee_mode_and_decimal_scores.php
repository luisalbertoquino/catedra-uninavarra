<?php

use App\Models\Edition;
use App\Models\RubricCriterion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Medidas del consolidado de calificación de video pitch 2026-1:
 *  - Comité técnico = promedio simple de los jurados y el Profesor experto (el experto pesa igual que un jurado).
 *  - Nota final = comité 70 % + likes 30 %.
 *  - Likes → nota por rangos con decimales (0-14 = 3 · 15-49 = 3,5 · 50-119 = 4 · 120-197 = 4,5 · 198+ = 5).
 *  - Los jurados califican cada criterio con decimales (ej. 4,2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            // separado: jurados y experto con pesos propios · promedio: el experto cuenta como un jurado más
            $table->string('committee_mode', 10)->default('promedio')->after('expert_score_weight');
        });

        DB::statement('ALTER TABLE evaluation_scores MODIFY score DECIMAL(4,1) UNSIGNED NOT NULL');

        // Las ediciones que aún no publican resultados adoptan las medidas establecidas
        DB::table('editions')->where('results_published', false)->update([
            'committee_mode' => 'promedio',
            'community_weight' => 30,
            'jury_weight' => 70,
            'expert_score_weight' => 0,
            'vote_grade_mode' => 'rangos',
            'vote_grade_thresholds' => json_encode(Edition::DEFAULT_VOTE_RANGES),
        ]);
        DB::table('editions')->where('results_published', true)->update(['committee_mode' => 'separado']);

        // Descriptores oficiales de la rúbrica en las ediciones en curso
        $open = DB::table('editions')->where('results_published', false)->pluck('id');
        foreach (RubricCriterion::DEFAULTS as [$name, $description]) {
            DB::table('rubric_criteria')->whereIn('edition_id', $open)->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->update(['description' => $description]);
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE evaluation_scores MODIFY score TINYINT UNSIGNED NOT NULL');

        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn('committee_mode');
        });
    }
};
