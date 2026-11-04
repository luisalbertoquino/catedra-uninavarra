<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Perfiles de los inversionistas invitados al evento (foto, cargo y trayectoria) y portafolios de cada
 * convocatoria (página web y/o PDF). Se cargan los primeros datos entregados por la Cátedra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investor_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('headline', 160)->nullable();
            $table->text('bio')->nullable();
            $table->string('expertise', 255)->nullable(); // temas separados por coma
            $table->string('photo')->nullable();
            $table->string('linkedin')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('period', 20)->nullable();
            $table->string('description', 500)->nullable();
            $table->string('url')->nullable();
            $table->string('pdf')->nullable();
            $table->boolean('is_featured')->default(false); // el del botón flotante
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('investor_profiles')->insert([
            [
                'name' => 'Alexander Quintero Bonilla',
                'headline' => 'Doctor en Administración · Investigador y consultor empresarial',
                'expertise' => 'Estrategia empresarial, Historia empresarial, Emprendimiento, Alta gerencia',
                'bio' => "Administrador de empresas, con estudios de especialización en Alta Gerencia, maestría en Ciencias de la Organización e Historia; doctorado en Administración. Profesor universitario de amplia trayectoria y reconocimiento. Investigador en temas de historia empresarial, emprendimiento y estrategia empresarial. Asesor, consultor y gestor empresarial de los colegios Gimnasio Infantil Mi Alegría de Vivir y Canadian School Rivera.\nAltamente comprometido con el aprendizaje continuo y el desarrollo social y cultural. Miembro de número y expresidente de la Academia Huilense de Historia. Con amplia experiencia en la dirección de empresas del sector privado y público: Cooperativa de Impresores del Sur, Advisor & Training S.A., Equipo As Ltda. y Grupo ARA S.A.S.",
                'photo' => '/images/inversionistas/alexander-quintero-bonilla.webp',
                'sort' => 1,
            ],
            [
                'name' => 'Dairo Andrés Rivera',
                'headline' => 'Empresario · Fundador de RIVART Soluciones Alimentarias S.A.S.',
                'expertise' => 'Empresas familiares, Innovación, Liderazgo, Gobierno corporativo',
                'bio' => "Empresario, líder organizacional y fundador de RIVART Soluciones Alimentarias S.A.S., con más de 12 años de experiencia en gestión empresarial, transformación de empresas familiares, innovación y liderazgo de equipos. Magíster en Emprendimiento e Innovación, Máster en Liderazgo Internacional y especialista en institucionalización y profesionalización de empresas familiares, con experiencia en relevo generacional y gobierno corporativo.\nHa liderado procesos de transformación organizacional, innovación de marca y expansión comercial. Se desempeña como speaker, formador empresarial y docente universitario en administración, emprendimiento y liderazgo. Actualmente, es consultor empresarial de la Cámara de Comercio del Huila y acompaña el diagnóstico y desarrollo estratégico de Rivera y Cía. S.A.S., empresa gastronómica con más de 32 años de trayectoria, fortaleciendo sus áreas financieras, administrativas, de gobierno corporativo y optimización operativa.",
                'photo' => '/images/inversionistas/dairo-andres-rivera.webp',
                'sort' => 2,
            ],
            [
                'name' => 'Juan Diego Durán Hernández',
                'headline' => 'Banca de inversión y capital privado',
                'expertise' => 'Banca de inversión, Capital privado, Estructuración de inversiones, Fundraising',
                'bio' => "Ejecutivo bilingüe (español-inglés) con más de 15 años de experiencia en banca de inversión, fondos de capital privado y estructuración de inversiones en Colombia y Latinoamérica. Amplio conocimiento del ecosistema financiero, con una red de alto nivel en entidades públicas, privadas, HNWIs y family offices.\nTrayectoria destacada en la originación, estructuración y gestión de vehículos de inversión, así como en la construcción de relaciones estratégicas y comunicación con inversionistas. Liderazgo probado en fundraising, desarrollo de negocios y toma de decisiones en contextos complejos y altamente regulados.",
                'photo' => '/images/inversionistas/juan-diego-duran-hernandez.webp',
                'sort' => 3,
            ],
        ]);

        DB::table('portfolios')->insert([
            [
                'title' => 'Portafolio LION HUB 2026-2', 'period' => '2026-2',
                'description' => 'Resumen de los emprendimientos de la convocatoria 2026-2: idea, equipo, misión, visión y montos.',
                'url' => 'https://uninavarra.edu.co/portafolio-lion-hub-2026-2/', 'pdf' => '/portafolio-lion-2-3.pdf',
                'is_featured' => true, 'sort' => 1,
            ],
            [
                'title' => 'Portafolio LION HUB 2026-1', 'period' => '2026-1',
                'description' => 'Emprendimientos de la convocatoria 2026-1.',
                'url' => 'https://uninavarra.edu.co/portafolio-lion-hub-2026', 'pdf' => null,
                'is_featured' => false, 'sort' => 2,
            ],
        ]);

        foreach (['investor_profiles', 'portfolios'] as $table) {
            DB::table($table)->update(['is_published' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolios');
        Schema::dropIfExists('investor_profiles');
    }
};
