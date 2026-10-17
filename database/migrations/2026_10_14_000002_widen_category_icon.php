<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Los iconos pasan de emoji a nombres de Font Awesome (ej. "screwdriver-wrench")
    public function up(): void
    {
        DB::statement('ALTER TABLE categories MODIFY icon VARCHAR(40) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE categories MODIFY icon VARCHAR(16) NULL');
    }
};
