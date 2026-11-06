<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Dos inversionistas invitados más, entregados por la Cátedra. Si ya existen (por nombre), no se duplican. */
return new class extends Migration
{
    private const PROFILES = [
        [
            'name' => 'Dario Huertas Murcia',
            'headline' => 'Fundador y Gerente General de Distriservicios ESP',
            'expertise' => 'Gerencia de proyectos, Ingeniería, Servicios públicos, Gas natural',
            'bio' => 'Ingeniero Mecánico, Magíster en Gerencia Integral de Proyectos, con 20 años de experiencia en formulación, ejecución y operación de sistemas de distribución de gas combustible por redes. Fundador y Gerente General de Distriservicios ESP desde agosto de 2017.',
            'photo' => '/images/inversionistas/dario-huertas-murcia.webp',
        ],
        [
            'name' => 'Bruno Lustenberger',
            'headline' => 'Economista · Fundador de BC INVESTMENT (Suiza)',
            'expertise' => 'Asesoría financiera, Economía, Proyectos en América Latina, Emprendimiento',
            'bio' => 'Economista suizo con posgrado en deportes y gestión deportiva. Fue formador en la división deportiva del Ejército suizo, funcionario público y profesor de economía, asesor financiero, emprendedor y desarrollador de proyectos en América Latina. Habla cinco idiomas, es escritor de tres libros y fundador de la empresa BC INVESTMENT en Suiza.',
            'photo' => '/images/inversionistas/bruno-lustenberger.webp',
        ],
    ];

    public function up(): void
    {
        $sort = (int) DB::table('investor_profiles')->max('sort');
        foreach (self::PROFILES as $profile) {
            if (DB::table('investor_profiles')->where('name', $profile['name'])->exists()) {
                continue;
            }
            DB::table('investor_profiles')->insert($profile + [
                'sort' => ++$sort, 'is_published' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('investor_profiles')->whereIn('name', array_column(self::PROFILES, 'name'))->delete();
    }
};
