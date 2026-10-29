<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Votación de la comunidad fuera de la página (likes en YouTube, Facebook…): la edición puede
 * deshabilitar el voto en la página y un administrador registra a mano los votos de cada emprendimiento.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->boolean('web_voting')->default(true)->after('max_votes_per_user');
        });
        Schema::table('ventures', function (Blueprint $table) {
            $table->unsignedInteger('external_votes')->default(0)->after('committee_score');
        });
    }

    public function down(): void
    {
        Schema::table('editions', fn (Blueprint $table) => $table->dropColumn('web_voting'));
        Schema::table('ventures', fn (Blueprint $table) => $table->dropColumn('external_votes'));
    }
};
