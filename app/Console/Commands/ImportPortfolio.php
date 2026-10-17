<?php

namespace App\Console\Commands;

use App\Models\Edition;
use App\Services\PortfolioImporter;
use Illuminate\Console\Command;

class ImportPortfolio extends Command
{
    protected $signature = 'catedra:importar {archivo : Ruta del Excel del portafolio}
                            {--edicion= : Slug de la edición (por defecto la activa)}
                            {--logos= : Carpeta con los logos (nombrados como cada emprendimiento)}
                            {--simular : Solo muestra lo que se importaría, sin guardar}';

    protected $description = 'Importa los emprendimientos de una edición desde el Excel del portafolio';

    public function handle(PortfolioImporter $importer): int
    {
        $edition = $this->option('edicion') ? Edition::where('slug', $this->option('edicion'))->first() : Edition::current();
        if (! $edition) {
            $this->error('No se encontró la edición.');

            return self::FAILURE;
        }

        $ventures = $importer->parse($this->argument('archivo'));
        $this->table(['Grupo', 'Emprendimiento', 'Integrantes', 'Monto inicio', 'Solicita', 'Logo en Excel'], collect($ventures)->map(fn ($v) => [
            $v['group'], $v['name'], count($v['members']),
            $v['startup_amount'] ? number_format($v['startup_amount'], 0, ',', '.') : '—',
            $v['funding_goal'] ? number_format($v['funding_goal'], 0, ',', '.') : '—',
            $v['embedded_logo'] ? 'sí' : '—',
        ]));

        if ($this->option('simular')) {
            $this->info('Simulación: no se guardó nada.');

            return self::SUCCESS;
        }

        $logos = $this->option('logos') ? glob(rtrim($this->option('logos'), '/\\').'/*.{png,jpg,jpeg,webp,PNG,JPG,JPEG,WEBP}', GLOB_BRACE) : [];
        $report = $importer->import($edition, $ventures, $logos);

        $this->info(count($report['created']).' creados, '.count($report['updated']).' actualizados, '.$report['logos'].' logos asignados en '.$edition->name.'.');
        foreach ($report['warnings'] as $w) {
            $this->warn('  · '.$w);
        }

        return self::SUCCESS;
    }
}
