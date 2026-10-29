<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Ajustes de contenido pedidos el 2026-10-29:
 * - Teléfono del Centro de Emprendimiento: +57 318 071 5286 (solo si seguía el anterior).
 * - "la Cátedra UNINAVARRA" pasa a "Cátedra UNINAVARRA" en textos de configuración y de las ediciones.
 * - Lugar de la gran final de la edición activa: "UNINAVARRA Neiva".
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('key', 'investment_contact_phone')->where('value', '+57 322 385 9086')
            ->update(['value' => '+57 318 071 5286']);

        foreach (DB::table('settings')->where('value', 'like', '%átedra UNINAVARRA%')->get() as $row) {
            DB::table('settings')->where('key', $row->key)->update(['value' => $this->withoutArticle($row->value)]);
        }
        foreach (DB::table('editions')->get(['id', 'description', 'tagline']) as $e) {
            DB::table('editions')->where('id', $e->id)->update([
                'description' => $this->withoutArticle($e->description),
                'tagline' => $this->withoutArticle($e->tagline),
            ]);
        }

        DB::table('editions')->where('is_active', true)->update(['final_location' => 'UNINAVARRA Neiva']);

        Cache::forget('site-settings');
    }

    public function down(): void
    {
        // Cambio de contenido: no se revierte
    }

    private function withoutArticle(?string $text): ?string
    {
        return $text === null ? null : preg_replace('/\b[Ll]a (C[áa]tedra UNINAVARRA)/u', '$1', $text);
    }
};
