<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Edition;
use App\Models\SupportPledge;
use App\Models\User;
use App\Models\Venture;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Datos de DEMOSTRACIÓN para la propuesta. Los emprendimientos, personas y cifras son ficticios.
 * Usuarios: admin@uninavarra.edu.co / password · inversionista@demo.co / password
 */
class DatabaseSeeder extends Seeder
{
    private array $programs = ['Administración de Empresas', 'Contaduría Pública', 'Ingeniería de Sistemas', 'Ingeniería Industrial', 'Derecho', 'Enfermería', 'Medicina', 'Psicología', 'Ingeniería Ambiental', 'Comunicación Social'];

    public function run(): void
    {
        $faker = \Faker\Factory::create('es_ES');

        $admin = User::create([
            'name' => 'Administrador Cátedra', 'email' => 'admin@uninavarra.edu.co', 'role' => 'admin',
            'password' => Hash::make('password'), 'email_verified_at' => now(),
        ]);
        $investor = User::create([
            'name' => 'Laura Inversora', 'email' => 'inversionista@demo.co', 'role' => 'inversionista',
            'organization' => 'Red de Ángeles Huila (demo)', 'phone' => '3001234567',
            'password' => Hash::make('password'), 'email_verified_at' => now(),
        ]);

        $community = collect(range(1, 70))->map(fn ($i) => User::create([
            'name' => $faker->firstName().' '.$faker->lastName(),
            'email' => "estudiante{$i}@uninavarra.edu.co",
            'role' => $i % 9 === 0 ? 'inversionista' : 'comunidad',
            'academic_program' => $faker->randomElement($this->programs),
            'organization' => $i % 9 === 0 ? $faker->company() : null,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]));

        $categories = collect([
            ['Tecnología', 'microchip'], ['Salud y bienestar', 'heart-pulse'], ['Agroindustria', 'seedling'],
            ['Sostenibilidad', 'recycle'], ['Alimentos y bebidas', 'mug-hot'], ['Educación', 'graduation-cap'],
            ['Moda y diseño', 'shirt'], ['Turismo', 'mountain-sun'], ['Servicios', 'screwdriver-wrench'],
        ])->mapWithKeys(fn ($c) => [$c[0] => Category::create(['name' => $c[0], 'slug' => Str::slug($c[0]), 'icon' => $c[1]])]);

        // ---------- Ediciones ----------
        $past1 = $this->edition('Cátedra UNINAVARRA 2025-2', 2025, 2, 'Lion Hub · Innovation Week', '2025-10-20', '2025-11-05', '2025-11-08 10:00', true);
        $past2 = $this->edition('Cátedra UNINAVARRA 2026-1', 2026, 1, 'Lion Hub · Build the Future', '2026-04-20', '2026-05-08', '2026-05-09 10:00', true);
        $current = $this->edition('Cátedra UNINAVARRA 2026-2', 2026, 2, 'Disruptive Minds', '2026-10-01', '2026-11-06 23:59', '2026-11-07 10:00', false, true);
        $current->update(['promo_video_url' => 'https://www.youtube.com/watch?v=0r4d-hZam6I']);

        $current->milestones()->createMany([
            ['title' => 'Congreso UNINAVARRA Emprende', 'description' => 'Tendencias y oportunidades para emprender en la región: IA, mercados, Fondo Emprender y panel de empresarios.', 'starts_at' => '2026-08-21', 'ends_at' => '2026-08-21', 'sort' => 0],
            ['title' => 'BootCamp de Ideación', 'description' => 'Equipos de cinco integrantes identifican problemas reales con análisis de causa raíz.', 'starts_at' => '2026-09-07', 'ends_at' => '2026-09-25', 'sort' => 1],
            ['title' => 'BootCamp de Validación', 'description' => 'Matriz de análisis de oportunidades con acompañamiento de mentores.', 'starts_at' => '2026-09-07', 'ends_at' => '2026-09-25', 'sort' => 2],
            ['title' => 'Webinar Storytelling & Elevator Pitch', 'description' => 'Cómo contar tu idea de negocio para enamorar a inversionistas.', 'starts_at' => '2026-09-28', 'ends_at' => '2026-10-02', 'sort' => 3],
            ['title' => 'Construcción del video pitch', 'description' => 'Cada equipo produce el video que vende su idea. Entrega: 23 de octubre antes del mediodía.', 'starts_at' => '2026-10-05', 'ends_at' => '2026-10-23', 'sort' => 4],
            ['title' => 'Votación de la comunidad', 'description' => 'La comunidad Navarrista vota por sus favoritos: aporta el 30 % de la evaluación.', 'starts_at' => '2026-10-01', 'ends_at' => '2026-11-06', 'sort' => 5],
            ['title' => 'LION HUB EVENT – Gran Final', 'description' => 'Los 5 finalistas sustentan ante un jurado de expertos inversionistas.', 'starts_at' => '2026-11-07', 'ends_at' => '2026-11-07', 'sort' => 6],
        ]);
        foreach ([$past1, $past2] as $p) {
            $p->milestones()->createMany([
                ['title' => 'Congreso UNINAVARRA Emprende', 'starts_at' => $p->voting_starts_at->copy()->subMonths(2), 'sort' => 0],
                ['title' => 'BootCamps de Ideación y Validación', 'starts_at' => $p->voting_starts_at->copy()->subMonths(1)->subWeeks(2), 'ends_at' => $p->voting_starts_at->copy()->subWeeks(4), 'sort' => 1],
                ['title' => 'Video pitch y votación', 'starts_at' => $p->voting_starts_at, 'ends_at' => $p->voting_ends_at, 'sort' => 2],
                ['title' => 'LION HUB EVENT – Gran Final', 'starts_at' => $p->final_at, 'sort' => 3],
            ]);
        }

        // ---------- Emprendimientos ----------
        $currentVentures = [
            ['CaféLab Huila', 'Alimentos y bebidas', 'Cafés especiales del Huila con trazabilidad del grano a la taza', 'Los caficultores venden su café como commodity y pierden hasta 60 % del valor que el cliente final paga.', 'Plataforma de suscripción de cafés de origen con QR que muestra la finca, el productor y el perfil de taza.'],
            ['AgroSense', 'Agroindustria', 'Sensores de bajo costo para riego inteligente', 'Los pequeños productores riegan "a ojo" y desperdician agua y fertilizantes en época seca.', 'Sensores de humedad conectados a una app que indica cuándo y cuánto regar, con alertas por WhatsApp.'],
            ['TutorIA', 'Educación', 'Tutor con inteligencia artificial para pruebas Saber', 'Los estudiantes de colegios públicos de la región no tienen acceso a preparación personalizada.', 'Asistente conversacional que diagnostica debilidades y genera rutas de estudio adaptativas.'],
            ['EcoEmpaque', 'Sostenibilidad', 'Empaques biodegradables a partir de residuos de plátano', 'Los restaurantes de Neiva usan icopor que tarda más de 500 años en degradarse.', 'Platos y contenedores fabricados con fibra de vástago de plátano, compostables en 90 días.'],
            ['MediTrack', 'Salud y bienestar', 'Recordatorios y seguimiento de medicamentos para adultos mayores', 'Uno de cada dos adultos mayores olvida dosis de su tratamiento crónico.', 'Pastillero inteligente + app para familiares con alertas y reporte al médico tratante.'],
            ['Rutas Tatacoa', 'Turismo', 'Experiencias astronómicas y de aventura en el desierto', 'El turismo en la Tatacoa es informal y poco seguro para visitantes extranjeros.', 'Marketplace de guías certificados con reservas en línea, seguros y experiencias de astroturismo.'],
            ['Cacao Vivo', 'Alimentos y bebidas', 'Chocolates bean-to-bar de cacao huilense', 'El cacao de la región se exporta sin transformar y el productor recibe muy poco.', 'Línea de chocolates premium con comercio justo directo con asociaciones campesinas.'],
            ['SolarFarm Neiva', 'Sostenibilidad', 'Energía solar compartida para barrios', 'Las tarifas de energía en el Huila están entre las más altas del país.', 'Granjas solares comunitarias por suscripción que reducen la factura hasta un 25 %.'],
            ['Moda Circular Opita', 'Moda y diseño', 'Ropa reciclada con identidad cultural huilense', 'La industria textil genera toneladas de ropa desechada cada mes en la ciudad.', 'Colecciones upcycling con estampados inspirados en el San Pedro y el sombrero suaceño.'],
            ['PetCare Express', 'Servicios', 'Veterinaria a domicilio en menos de 60 minutos', 'Las urgencias veterinarias nocturnas no tienen cobertura en la ciudad.', 'Red de veterinarios a domicilio con app de agendamiento y telemedicina 24/7.'],
            ['Bambú Construye', 'Sostenibilidad', 'Vivienda modular sostenible en guadua', 'El déficit de vivienda rural en el Huila supera el 40 %.', 'Módulos prefabricados de guadua tratada, sismorresistentes y armables en 15 días.'],
            ['LegalBot', 'Tecnología', 'Orientación jurídica gratuita para emprendedores', 'Los microempresarios desconocen sus obligaciones legales y tributarias.', 'Chatbot que guía la formalización, contratos básicos y obligaciones ante la DIAN.'],
        ];
        $pastNames = [
            ['Achira Gourmet', 'Alimentos y bebidas'], ['NeivaBike', 'Servicios'], ['HidroPonix', 'Agroindustria'], ['SaludMental Ya', 'Salud y bienestar'],
            ['Artesanías del Magdalena', 'Moda y diseño'], ['CodeKids', 'Educación'], ['ReciclApp', 'Sostenibilidad'], ['Huila Travel', 'Turismo'],
            ['FarmaRural', 'Salud y bienestar'], ['Panela Fit', 'Alimentos y bebidas'], ['DronAgro', 'Tecnología'], ['ContaFácil', 'Servicios'],
        ];

        $ventures = collect();
        foreach ($currentVentures as $i => [$name, $cat, $tagline, $problem, $solution]) {
            $ventures->push($this->venture($current, $categories[$cat], $name, $tagline, $problem, $solution, $faker));
        }
        foreach (array_chunk($pastNames, 6) as $chunkIdx => $chunk) {
            $edition = $chunkIdx === 0 ? $past1 : $past2;
            foreach ($chunk as $pos => [$name, $cat]) {
                $v = $this->venture($edition, $categories[$cat], $name, $faker->sentence(8), $faker->paragraph(2), $faker->paragraph(2), $faker);
                $v->update([
                    'committee_score' => $faker->numberBetween(60, 95),
                    'is_finalist' => $pos < 5,
                    'final_position' => $pos < 3 ? $pos + 1 : null,
                ]);
            }
        }

        // ---------- Votos, comentarios y apoyos de la edición actual ----------
        $weights = [38, 30, 26, 22, 20, 17, 15, 12, 10, 8, 6, 4];
        $opinions = [
            '¡Qué idea tan buena! Esto lo necesita la región.', 'Me encantó el video pitch, muy claro y convincente.',
            'Les recomiendo validar el precio con más clientes, pero la propuesta es excelente.', 'Ya quiero probar el producto 😍',
            'Muy bien pensado el modelo de negocio. ¿Tienen pensado expandirse a otros municipios?', 'Gran trabajo en equipo, se nota la dedicación.',
            'La solución es innovadora y tiene impacto social real.', 'Sería genial una alianza con la Gobernación.',
            'Como cliente potencial me interesa mucho. ¿Cuándo lanzan?', 'Excelente uso de la tecnología para un problema local.',
            'Tienen mi voto. ¡Vamos Navarristas!', 'Faltaría ver más sobre la competencia, pero me gusta.',
        ];
        $start = Carbon::parse('2026-10-01');
        foreach ($community as $user) {
            $picked = collect($ventures)->keys()->shuffle()->sortByDesc(fn ($k) => $weights[$k] * mt_rand(1, 10))->take(1); // un voto por persona
            foreach ($picked as $k) {
                $date = $start->copy()->addDays(mt_rand(0, 9))->addHours(mt_rand(7, 22))->addMinutes(mt_rand(0, 59))->min(now()->subMinutes(mt_rand(5, 300)));
                Vote::create(['user_id' => $user->id, 'venture_id' => $ventures[$k]->id, 'edition_id' => $current->id, 'created_at' => $date, 'updated_at' => $date]);
            }
            if (mt_rand(1, 100) <= 45) {
                $date = $start->copy()->addDays(mt_rand(0, 9))->addHours(mt_rand(7, 22))->min(now()->subMinutes(mt_rand(5, 600)));
                Comment::create([
                    'user_id' => $user->id, 'venture_id' => $ventures[$picked->first()]->id,
                    'body' => $faker->randomElement($opinions), 'rating' => mt_rand(3, 5),
                    'created_at' => $date, 'updated_at' => $date,
                ]);
            }
        }

        $investors = $community->where('role', 'inversionista')->push($investor);
        $types = array_keys(SupportPledge::TYPES);
        foreach ($investors as $inv) {
            foreach ($ventures->random(2) as $v) {
                $picked = SupportPledge::normalizeTypes($faker->randomElements($types, mt_rand(1, 3)));
                $type = $picked[0];
                SupportPledge::create([
                    'user_id' => $inv->id, 'venture_id' => $v->id, 'type' => $type, 'types' => $picked,
                    'amount' => in_array($type, SupportPledge::MONETARY) ? $faker->randomElement([500000, 1000000, 2000000, 5000000, 10000000]) : null,
                    'message' => $faker->randomElement([
                        'Me interesa conocer el plan financiero y la proyección a 3 años.',
                        'Podemos apoyarles con mentoría en mercadeo digital y ventas.',
                        'Nuestra empresa podría ser su primer cliente corporativo.',
                        'Quisiera agendar una reunión con el equipo después de la gran final.',
                    ]),
                    'status' => $faker->randomElement(['pendiente', 'pendiente', 'contactado', 'en_proceso']),
                ]);
            }
        }

        $ventures->each(fn ($v) => $v->update(['views' => mt_rand(40, 900)]));

        // Jurados, rúbrica, evaluaciones y ranking de inversionistas
        $this->call(JuryDemoSeeder::class);
    }

    private function edition(string $name, int $year, int $period, string $tagline, string $votingStart, string $votingEnd, string $final, bool $results, bool $active = false): Edition
    {
        return Edition::create([
            'name' => $name, 'slug' => Str::slug($name), 'year' => $year, 'period' => $period,
            'event_name' => 'LION HUB EVENT', 'tagline' => $tagline,
            'description' => 'La Cátedra UNINAVARRA desarrolla las competencias emprendedoras de los estudiantes Navarristas mediante el aprender haciendo y pedagogías activas. Los equipos construyen propuestas de negocio basadas en innovación y sostenibilidad, y las presentan a la comunidad y a un jurado de expertos inversionistas.',
            'voting_starts_at' => $votingStart, 'voting_ends_at' => $votingEnd, 'final_at' => $final,
            'final_location' => 'Auditorio Ciudad Universitaria UNINAVARRA, Neiva',
            'community_weight' => 30, 'max_votes_per_user' => 1, 'finalists_count' => 5,
            // Ediciones anteriores con el esquema 30/40/30; las nuevas con el del consolidado 2026-1 (comité 70 %, likes por rangos)
            ...($results
                ? ['jury_weight' => 40, 'expert_score_weight' => 30, 'committee_mode' => 'separado', 'vote_grade_mode' => 'relativo']
                : ['jury_weight' => 70, 'expert_score_weight' => 0, 'committee_mode' => 'promedio', 'vote_grade_mode' => 'rangos', 'vote_grade_thresholds' => Edition::DEFAULT_VOTE_RANGES]),
            'is_active' => $active, 'is_published' => true, 'results_published' => $results,
        ]);
    }

    private function venture(Edition $edition, Category $category, string $name, string $tagline, string $problem, string $solution, $faker): Venture
    {
        $slug = Str::slug($name).($edition->is_active ? '' : '-'.$edition->period_label);
        $program = $faker->randomElement($this->programs);

        $venture = Venture::create([
            'edition_id' => $edition->id, 'category_id' => $category->id,
            'name' => $name, 'slug' => $slug, 'tagline' => $tagline,
            'description' => "$name nace en el aula de la Cátedra UNINAVARRA como respuesta a una necesidad real de nuestra región. ".$solution.' Nuestro equipo combina perfiles de distintas disciplinas para llevar la idea del prototipo al mercado.',
            'problem' => $problem, 'solution' => $solution,
            'target_market' => 'Inicialmente Neiva y municipios del Huila, con proyección al sur colombiano. Clientes: '.$faker->randomElement(['hogares de estratos 2 a 4', 'pequeños productores agrícolas', 'restaurantes y cafeterías', 'instituciones educativas', 'turistas nacionales y extranjeros', 'pymes de la región']).'.',
            'cover' => "https://picsum.photos/seed/{$slug}-cover/1600/900",
            'academic_program' => $program,
            'contact_name' => $faker->firstName().' '.$faker->lastName(),
            'contact_email' => Str::slug($name, '').'@demo.co',
            'contact_phone' => '3'.$faker->numerify('#########'),
            'instagram' => 'https://instagram.com/'.Str::slug($name, ''),
            'funding_goal' => $faker->randomElement([5000000, 10000000, 15000000, 25000000, 40000000]),
            'is_published' => true,
        ]);

        $roles = ['CEO', 'Líder comercial', 'Líder de producto', 'Finanzas', 'Marketing'];
        foreach ($roles as $i => $role) {
            $venture->members()->create([
                'name' => $faker->firstName().' '.$faker->lastName(),
                'role' => $role, 'academic_program' => $i === 0 ? $program : $faker->randomElement($this->programs), 'sort' => $i,
            ]);
        }
        foreach (range(1, 4) as $i) {
            $venture->images()->create(['path' => "https://picsum.photos/seed/{$slug}-{$i}/1200/800", 'sort' => $i]);
        }

        return $venture;
    }
}
