<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Una intención de apoyo puede ofrecer varios tipos a la vez (ej. equipos + herramientas + mercadeo).
 * `type` se conserva como tipo principal (el primero según el orden de SupportPledge::TYPES) para filtros y montos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_pledges', function (Blueprint $table) {
            $table->json('types')->nullable()->after('type');
        });
        DB::statement('UPDATE support_pledges SET types = JSON_ARRAY(type)');
        DB::statement('ALTER TABLE support_pledges MODIFY type VARCHAR(30) NOT NULL');
    }

    public function down(): void
    {
        Schema::table('support_pledges', function (Blueprint $table) {
            $table->dropColumn('types');
        });
    }
};
