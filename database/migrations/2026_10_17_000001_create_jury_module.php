<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jurado especial (ej. "Profesor experto") con más peso en el puntaje del comité
        Schema::table('editions', function (Blueprint $table) {
            $table->string('expert_title')->default('Profesor experto')->after('finalists_count');
            $table->decimal('expert_weight', 4, 2)->default(2)->after('expert_title'); // multiplicador frente a un jurado normal
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_expert')->default(false)->after('role');
            $table->boolean('show_in_investor_ranking')->default(false)->after('academic_program');
        });

        // Criterios de la rúbrica de evaluación de cada edición
        Schema::create('rubric_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('weight')->default(20);    // % dentro de la rúbrica
            $table->unsignedTinyInteger('max_score')->default(5);  // escala 1..max_score
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        // Evaluación de un jurado sobre un emprendimiento
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venture_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_score', 5, 2)->default(0); // 0-100 ponderado según la rúbrica
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['venture_id', 'user_id']);
        });

        Schema::create('evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rubric_criterion_id')->constrained('rubric_criteria')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->unique(['evaluation_id', 'rubric_criterion_id']);
        });

        // Un solo voto por persona en cada edición
        DB::statement('ALTER TABLE editions ALTER max_votes_per_user SET DEFAULT 1');
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_scores');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('rubric_criteria');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['is_expert', 'show_in_investor_ranking']));
        Schema::table('editions', fn (Blueprint $t) => $t->dropColumn(['expert_title', 'expert_weight']));
        DB::statement('ALTER TABLE editions ALTER max_votes_per_user SET DEFAULT 3');
    }
};
