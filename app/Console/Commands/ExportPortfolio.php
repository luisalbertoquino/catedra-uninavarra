<?php

namespace App\Console\Commands;

use App\Models\Edition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Exporta una edición (configuración + emprendimientos + equipos + logos) a database/data/,
 * para cargarla en otro servidor con: php artisan db:seed --class=PortafolioRealSeeder
 * La carpeta database/data/ está fuera de git porque contiene datos personales.
 */
class ExportPortfolio extends Command
{
    protected $signature = 'catedra:exportar-portafolio {--edicion= : Slug de la edición (por defecto la activa)}';

    protected $description = 'Exporta los emprendimientos reales de una edición a database/data para sembrarlos en otro servidor';

    public function handle(): int
    {
        $edition = $this->option('edicion') ? Edition::where('slug', $this->option('edicion'))->first() : Edition::current();
        if (! $edition) {
            $this->error('No se encontró la edición.');

            return self::FAILURE;
        }
        $edition->load(['milestones', 'criteria', 'ventures' => fn ($q) => $q->orderBy('id')->with(['category', 'members'])]);

        $dir = database_path('data');
        $logoDir = "{$dir}/logos/{$edition->slug}";
        File::ensureDirectoryExists($logoDir);
        File::cleanDirectory($logoDir);

        $ventures = $edition->ventures
            ->reject(fn ($v) => Str::startsWith((string) $v->cover, 'https://picsum.photos/')) // nunca exportar los ficticios
            ->values()
            ->map(function ($v) use ($logoDir) {
                $logo = null;
                if ($v->logo && ! Str::startsWith($v->logo, 'http') && Storage::disk('public')->exists($v->logo)) {
                    $logo = $v->slug.'.'.pathinfo($v->logo, PATHINFO_EXTENSION);
                    File::copy(Storage::disk('public')->path($v->logo), "{$logoDir}/{$logo}");
                }

                return [
                    'slug' => $v->slug, 'name' => $v->name, 'team_group' => $v->team_group,
                    'category' => $v->category ? ['slug' => $v->category->slug, 'name' => $v->category->name, 'icon' => $v->category->icon] : null,
                    'tagline' => $v->tagline, 'description' => $v->description, 'problem' => $v->problem, 'solution' => $v->solution,
                    'target_market' => $v->target_market, 'mission' => $v->mission, 'vision' => $v->vision,
                    'academic_program' => $v->academic_program, 'video_url' => $v->video_url,
                    'website' => $v->website, 'instagram' => $v->instagram, 'facebook' => $v->facebook, 'tiktok' => $v->tiktok,
                    'contact_name' => $v->contact_name, 'contact_email' => $v->contact_email, 'contact_phone' => $v->contact_phone,
                    'startup_amount' => $v->startup_amount, 'funding_goal' => $v->funding_goal, 'is_published' => $v->is_published,
                    'logo' => $logo,
                    'members' => $v->members->map(fn ($m) => $m->only('name', 'role', 'academic_program', 'linkedin'))->values(),
                ];
            });

        $data = [
            'exported_at' => now()->toIso8601String(),
            'edition' => array_merge($edition->only([
                'name', 'slug', 'year', 'period', 'event_name', 'tagline', 'description', 'promo_video_url',
                'final_location', 'community_weight', 'jury_weight', 'expert_score_weight', 'committee_mode', 'vote_grade_mode', 'vote_grade_thresholds',
                'max_votes_per_user', 'finalists_count', 'voting_email_domain', 'expert_title', 'is_active', 'is_published',
            ]), [
                'voting_starts_at' => $edition->voting_starts_at?->toDateTimeString(),
                'voting_ends_at' => $edition->voting_ends_at?->toDateTimeString(),
                'final_at' => $edition->final_at?->toDateTimeString(),
                'milestones' => $edition->milestones->map(fn ($m) => [
                    'title' => $m->title, 'description' => $m->description,
                    'starts_at' => $m->starts_at?->toDateString(), 'ends_at' => $m->ends_at?->toDateString(),
                ])->values(),
                'criteria' => $edition->criteria->map->only('name', 'description', 'weight', 'max_score')->values(),
            ]),
            'ventures' => $ventures,
        ];

        $file = "{$dir}/portafolio-{$edition->slug}.json";
        File::put($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->info("Exportados {$ventures->count()} emprendimientos de {$edition->name}");
        $this->line("  · {$file}");
        $this->line('  · '.count(File::files($logoDir))." logos en {$logoDir}");

        return self::SUCCESS;
    }
}
