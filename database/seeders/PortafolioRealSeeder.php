<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Edition;
use App\Models\Venture;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Carga los emprendimientos REALES exportados con `php artisan catedra:exportar-portafolio`.
 *
 *   php artisan db:seed --class=PortafolioRealSeeder
 *
 * Lee database/data/portafolio-*.json y los logos de database/data/logos/{edición}/.
 * Es idempotente: crea o actualiza la edición, sus categorías y emprendimientos (por grupo o slug),
 * reemplaza los integrantes y elimina de esa edición los emprendimientos ficticios del seeder de demo.
 * Los votos, comentarios, apoyos y evaluaciones reales que ya existan no se tocan.
 */
class PortafolioRealSeeder extends Seeder
{
    public function run(): void
    {
        $files = File::glob(database_path('data/portafolio-*.json'));
        if (! $files) {
            $this->command?->warn('No hay archivos en database/data/. Expórtalos con: php artisan catedra:exportar-portafolio');

            return;
        }

        foreach ($files as $file) {
            $data = json_decode(File::get($file), true, flags: JSON_THROW_ON_ERROR);
            DB::transaction(fn () => $this->load($data));
        }
    }

    private function load(array $data): void
    {
        $e = $data['edition'];
        $edition = Edition::firstWhere('slug', $e['slug']);
        $isNew = ! $edition;
        $attributes = collect($e)->except(['milestones', 'criteria'])->all();
        $edition ??= new Edition;
        $edition->fill($attributes)->save();

        // Cronograma y rúbrica solo si la edición es nueva o no los tiene (no pisar ajustes del admin)
        if ($isNew || $edition->milestones()->doesntExist()) {
            foreach ($e['milestones'] as $i => $m) {
                $edition->milestones()->create($m + ['sort' => $i]);
            }
        }
        if ($isNew || $edition->criteria()->doesntExist()) {
            foreach ($e['criteria'] as $i => $c) {
                $edition->criteria()->create($c + ['sort' => $i]);
            }
        }
        if ($e['is_active'] ?? false) {
            Edition::whereKeyNot($edition->id)->update(['is_active' => false]);
        }

        // Quitar los emprendimientos ficticios (portada de picsum.photos) de esta edición
        $removed = $edition->ventures()->where('cover', 'like', 'https://picsum.photos/%')->get()->each->delete()->count();

        $logoDir = database_path("data/logos/{$e['slug']}");
        $created = $updated = 0;
        foreach ($data['ventures'] as $v) {
            $category = $v['category']
                ? Category::firstOrCreate(['slug' => $v['category']['slug']], ['name' => $v['category']['name'], 'icon' => $v['category']['icon']])
                : null;

            $venture = $edition->ventures()->where('team_group', $v['team_group'])->whereNotNull('team_group')->first()
                ?? Venture::firstWhere('slug', $v['slug'])
                ?? new Venture(['edition_id' => $edition->id]);
            $venture->exists ? $updated++ : $created++;

            $fields = collect($v)->except(['category', 'members', 'logo', 'slug'])->all();
            $venture->fill($fields + ['edition_id' => $edition->id, 'category_id' => $category?->id]);
            if (! $venture->exists) {
                $venture->slug = Venture::where('slug', $v['slug'])->exists() ? $v['slug'].'-'.Str::lower(Str::random(4)) : $v['slug'];
            }

            if ($v['logo'] && File::exists("{$logoDir}/{$v['logo']}")) {
                $path = 'ventures/logos/'.$v['logo'];
                Storage::disk('public')->put($path, File::get("{$logoDir}/{$v['logo']}"));
                $venture->logo = $path;
            }
            $venture->save();

            $venture->members()->delete();
            foreach ($v['members'] as $i => $m) {
                $venture->members()->create($m + ['sort' => $i]);
            }
        }

        $this->command?->info("{$edition->name}: {$created} creados, {$updated} actualizados, {$removed} ficticios eliminados.");
    }
}
