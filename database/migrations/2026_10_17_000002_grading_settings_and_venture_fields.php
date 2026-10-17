<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nota final (1 a 5) = comunidad % + jurados % + profesor experto %. Todo configurable por edición.
        Schema::table('editions', function (Blueprint $table) {
            $table->unsignedTinyInteger('jury_weight')->default(40)->after('community_weight');
            $table->unsignedTinyInteger('expert_score_weight')->default(30)->after('jury_weight');
            $table->string('vote_grade_mode', 10)->default('relativo')->after('expert_score_weight'); // relativo | rangos
            $table->json('vote_grade_thresholds')->nullable()->after('vote_grade_mode'); // votos mínimos para nota 2,3,4,5
            $table->dropColumn('expert_weight'); // reemplazado por expert_score_weight (porcentaje)
        });
        DB::table('editions')->update(['community_weight' => 30, 'jury_weight' => 40, 'expert_score_weight' => 30]);

        // Datos del portafolio de cada emprendimiento
        Schema::table('ventures', function (Blueprint $table) {
            $table->text('mission')->nullable()->after('target_market');
            $table->text('vision')->nullable()->after('mission');
            $table->decimal('startup_amount', 14, 2)->nullable()->after('funding_goal'); // monto estimado para iniciar
            $table->string('team_group', 40)->nullable()->after('academic_program');     // ej. "Grupo 7"
        });

        // Configuración general del sitio editable desde el admin
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 80)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::table('ventures', fn (Blueprint $t) => $t->dropColumn(['mission', 'vision', 'startup_amount', 'team_group']));
        Schema::table('editions', function (Blueprint $t) {
            $t->dropColumn(['jury_weight', 'expert_score_weight', 'vote_grade_mode', 'vote_grade_thresholds']);
            $t->decimal('expert_weight', 4, 2)->default(2);
        });
    }
};
