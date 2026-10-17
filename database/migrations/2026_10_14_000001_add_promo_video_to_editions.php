<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Video promocional (YouTube) que se reproduce de fondo en la portada
        Schema::table('editions', function (Blueprint $table) {
            $table->string('promo_video_url')->nullable()->after('banner');
        });
    }

    public function down(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn('promo_video_url');
        });
    }
};
