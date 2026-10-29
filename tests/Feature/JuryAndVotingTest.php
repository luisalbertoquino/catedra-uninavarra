<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Evaluation;
use App\Models\RubricCriterion;
use App\Models\SupportPledge;
use App\Models\User;
use App\Models\Venture;
use App\Services\InvestorRanking;
use App\Services\JuryScoring;
use App\Services\Ranking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JuryAndVotingTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::create([
            'name' => 'Edición de prueba', 'slug' => 'edicion-prueba', 'year' => 2026, 'period' => 2,
            'voting_starts_at' => now()->subDay(), 'voting_ends_at' => now()->addDays(10),
            'community_weight' => 30, 'jury_weight' => 40, 'expert_score_weight' => 30, 'committee_mode' => 'separado',
            'max_votes_per_user' => 1, 'expert_title' => 'Profesor experto', 'is_active' => true, 'is_published' => true,
        ]);
        foreach (RubricCriterion::DEFAULTS as $i => [$name]) {
            $this->edition->criteria()->create(['name' => $name, 'weight' => 20, 'max_score' => 5, 'sort' => $i]);
        }
    }

    private function venture(string $name): Venture
    {
        return Venture::create(['edition_id' => $this->edition->id, 'name' => $name, 'slug' => str($name)->slug(), 'is_published' => true]);
    }

    private function jury(bool $expert = false): User
    {
        return User::factory()->create(['role' => 'jurado', 'is_expert' => $expert]);
    }

    /** Envía una evaluación: un puntaje para todos los criterios o uno por criterio (en orden). */
    private function evaluate(User $jury, Venture $venture, int|float|array $score)
    {
        $scores = $this->edition->criteria->values()->mapWithKeys(fn ($c, $i) => [$c->id => is_array($score) ? $score[$i] : $score])->all();

        return $this->actingAs($jury)->post(route('jury.store', $venture), ['scores' => $scores, 'comment' => 'ok']);
    }

    public function test_only_juries_can_access_the_jury_panel(): void
    {
        $this->actingAs(User::factory()->create())->get('/jurado')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/jurado')->assertForbidden();
        $this->actingAs($this->jury())->get('/jurado')->assertOk();
        $this->actingAs($this->jury())->get('/admin')->assertForbidden();
    }

    public function test_jury_evaluation_is_saved_and_scored_with_the_rubric(): void
    {
        $venture = $this->venture('Agro Uno');
        $this->evaluate($this->jury(), $venture, 4)->assertRedirect(route('jury.show', $venture));

        $evaluation = Evaluation::first();
        $this->assertEquals(80.0, (float) $evaluation->total_score); // 4/5 en todos los criterios = nota 4,0
        $this->assertEquals(4.0, JuryScoring::forEdition($this->edition)->get($venture->id)['jury']);
        $this->assertCount(5, $evaluation->scores);
    }

    public function test_incomplete_or_out_of_scale_evaluations_are_rejected(): void
    {
        $venture = $this->venture('Agro Dos');
        $jury = $this->jury();
        $first = $this->edition->criteria->first();

        $this->actingAs($jury)->post(route('jury.store', $venture), ['scores' => [$first->id => 3]])->assertSessionHasErrors();
        $this->evaluate($jury, $venture, 9)->assertSessionHasErrors();
        $this->assertDatabaseCount('evaluations', 0);
    }

    public function test_jury_and_expert_grades_are_separate_components(): void
    {
        $venture = $this->venture('Agro Tres');
        $this->evaluate($this->jury(expert: true), $venture, 5); // experto: nota 5
        $this->evaluate($this->jury(), $venture, 2);              // jurado: nota 2
        $this->evaluate($this->jury(), $venture, 4);              // jurado: nota 4

        $s = JuryScoring::forEdition($this->edition)->get($venture->id);
        $this->assertEquals(3.0, $s['jury']);   // promedio de jurados normales
        $this->assertEquals(5.0, $s['expert']); // el experto es un componente aparte
    }

    public function test_final_grade_uses_30_40_30_weights(): void
    {
        $a = $this->venture('Alfa');
        $b = $this->venture('Beta');
        $this->evaluate($this->jury(), $a, 4);              // jurados 4
        $this->evaluate($expert = $this->jury(expert: true), $a, 5); // experto 5
        $this->evaluate($this->jury(), $b, 3);
        $this->evaluate($expert, $b, 3);
        $a->votes()->create(['user_id' => User::factory()->create()->id, 'edition_id' => $this->edition->id]);

        $ranking = Ranking::for($this->edition)->keyBy('slug');
        // Alfa: comunidad 5 (más votado) ×0,3 + jurados 4 ×0,4 + experto 5 ×0,3 = 4,6
        $this->assertEquals(5.0, $ranking['alfa']->community_grade);
        $this->assertEquals(4.6, $ranking['alfa']->final_grade);
        // Beta: comunidad 1 (0 votos) ×0,3 + 3 ×0,4 + 3 ×0,3 = 2,4
        $this->assertEquals(2.4, $ranking['beta']->final_grade);
        $this->assertFalse($ranking['alfa']->is_partial);
    }

    public function test_missing_components_are_reweighted_and_marked_partial(): void
    {
        $v = $this->venture('Gamma Parcial');
        $this->evaluate($this->jury(), $v, 4); // sin experto, sin votos (comunidad = 1)

        $row = Ranking::for($this->edition)->firstWhere('slug', 'gamma-parcial');
        // (1 ×30 + 4 ×40) / 70 = 2,71
        $this->assertEquals(2.71, $row->final_grade);
        $this->assertTrue($row->is_partial);
    }

    public function test_legacy_vote_thresholds_still_work(): void
    {
        $this->edition->update(['vote_grade_mode' => 'rangos', 'vote_grade_thresholds' => ['2' => 2, '3' => 4, '4' => 6, '5' => 8]]);

        $this->assertEquals(1.0, $this->edition->voteGrade(1, 100));
        $this->assertEquals(3.0, $this->edition->voteGrade(5, 100));
        $this->assertEquals(5.0, $this->edition->voteGrade(20, 100));

        $this->edition->update(['vote_grade_mode' => 'relativo']);
        $this->assertEquals(3.0, $this->edition->fresh()->voteGrade(5, 10)); // 1 + 4 × 5/10
    }

    public function test_likes_ranges_of_the_2026_1_consolidated_sheet(): void
    {
        $this->edition->update(['vote_grade_mode' => 'rangos', 'vote_grade_thresholds' => Edition::DEFAULT_VOTE_RANGES]);

        foreach ([0 => 3, 14 => 3, 15 => 3.5, 49 => 3.5, 50 => 4, 119 => 4, 120 => 4.5, 197 => 4.5, 198 => 5, 1527 => 5] as $likes => $grade) {
            $this->assertEquals($grade, $this->edition->voteGrade($likes, 1527), "{$likes} likes");
        }
    }

    /** Reproduce dos filas del consolidado de video pitch 2026-1: comité (experto + 2 jurados) / 3 × 70 % + likes × 30 % */
    public function test_final_grade_matches_the_2026_1_consolidated_sheet(): void
    {
        $this->edition->update([
            'committee_mode' => 'promedio', 'community_weight' => 30, 'jury_weight' => 70, 'expert_score_weight' => 0,
            'vote_grade_mode' => 'rangos', 'vote_grade_thresholds' => Edition::DEFAULT_VOTE_RANGES,
        ]);
        $expert = $this->jury(expert: true);
        [$tn, $vice] = [$this->jury(), $this->jury()];

        $neiva = $this->venture('Neiva Charge');      // experto 4 · jurados 5 y 4,2 · 4 likes (nota 3) → 3,98
        $this->evaluate($expert, $neiva, 4);
        $this->evaluate($tn, $neiva, 5);
        $this->evaluate($vice, $neiva, 4.2)->assertSessionHasNoErrors();
        User::factory()->count(4)->create()->each(fn ($u) => $neiva->votes()->create(['user_id' => $u->id, 'edition_id' => $this->edition->id]));

        $health = $this->venture('Health Life');      // experto 4,2 · 4 · 3,5 · 3,5 · 4 = 3,84 · jurados 3 y 3,8 · sin likes (nota 3) → 3,38
        $this->evaluate($expert, $health, [4.2, 4, 3.5, 3.5, 4])->assertSessionHasNoErrors();
        $this->evaluate($tn, $health, 3);
        $this->evaluate($vice, $health, 3.8);

        $ranking = Ranking::for($this->edition->fresh())->keyBy('slug');
        $this->assertEqualsWithDelta(4.4, $ranking['neiva-charge']->jury_grade, 0.001);
        $this->assertEquals(3.98, $ranking['neiva-charge']->final_grade);
        $this->assertEqualsWithDelta(3.84, $ranking['health-life']->expert_grade, 0.001);
        $this->assertEquals(3.38, $ranking['health-life']->final_grade);
        $this->assertFalse($ranking['health-life']->is_partial);
    }

    public function test_scores_allow_a_single_decimal(): void
    {
        $venture = $this->venture('Decimal');
        $this->evaluate($this->jury(), $venture, 4.25)->assertSessionHasErrors();
        $this->assertDatabaseCount('evaluations', 0);
    }

    /** Restaurante Internacional (2026-1): 3,5 · 3,5 · 3 · 3 · 0 = 2,6 — un criterio puede calificarse con 0 */
    public function test_a_criterion_can_be_scored_with_zero(): void
    {
        $venture = $this->venture('Restaurante');
        $this->evaluate($this->jury(expert: true), $venture, [3.5, 3.5, 3, 3, 0])->assertSessionHasNoErrors();
        $this->assertEqualsWithDelta(2.6, JuryScoring::forEdition($this->edition)->get($venture->id)['expert'], 0.001);
        $this->evaluate($this->jury(), $venture, -1)->assertSessionHasErrors();
    }

    public function test_admin_configures_committee_average_and_vote_ranges(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.editions.edit', $this->edition))->assertOk()->assertSee('Promedio del comité');

        $this->actingAs($admin)->put(route('admin.editions.update', $this->edition), [
            'name' => $this->edition->name, 'year' => 2026, 'community_weight' => 30, 'jury_weight' => 70, 'expert_score_weight' => 30,
            'committee_mode' => 'promedio', 'vote_grade_mode' => 'rangos', 'max_votes_per_user' => 1, 'finalists_count' => 5, 'expert_title' => 'Profesor experto',
            'vote_grade_thresholds' => [['min' => 50, 'grade' => 4], ['min' => 0, 'grade' => 3], ['min' => 198, 'grade' => 5]],
        ])->assertSessionHasNoErrors();

        $e = $this->edition->fresh();
        $this->assertSame(0, (int) $e->expert_score_weight); // en modo promedio el experto no tiene peso aparte
        $this->assertSame(['community' => 30, 'jury' => 70, 'expert' => 0], $e->gradeWeights());
        $this->assertSame([0, 50, 198], array_column($e->voteRanges(), 'min'));
        $this->assertEquals(4.0, $e->voteGrade(60, 100));

        $venture = $this->venture('Pantallas');
        $this->evaluate($expert = $this->jury(expert: true), $venture, 4.5);
        $this->actingAs($expert)->get(route('jury.show', $venture))->assertOk()->assertSee('pesa lo mismo que la de cada jurado');
        $this->actingAs($admin)->get(route('admin.results.index', ['edicion' => $e->id]))->assertOk()->assertSee('Comité técnico');
        $this->actingAs($admin)->get(route('admin.results.evaluations', $e))->assertOk();
        $this->get(route('ranking'))->assertOk();
    }

    public function test_jury_trial_command_creates_accounts_and_example_videos(): void
    {
        $withVideo = Venture::create(['edition_id' => $this->edition->id, 'name' => 'Con video', 'slug' => 'con-video', 'is_published' => true, 'video_url' => 'https://youtu.be/abcdefghijk']);
        $noVideo = $this->venture('Sin video');
        $demo = User::factory()->create(['role' => 'jurado', 'email' => 'jurado1@uninavarra.edu.co']);

        $this->artisan('catedra:jurados-prueba', ['--quitar-demo' => true, '--forzar' => true])->assertSuccessful();

        $this->assertModelMissing($demo);
        $this->assertSame(3, User::where('role', 'jurado')->count());
        $this->assertTrue(User::firstWhere('email', 'profesor.experto@uninavarra.edu.co')->is_expert);
        $this->assertSame(\App\Console\Commands\PrepareJuryTrial::VIDEO, $noVideo->fresh()->video_url);
        $this->assertSame('https://youtu.be/abcdefghijk', $withVideo->fresh()->video_url); // los videos reales no se tocan

        $this->evaluate(User::firstWhere('email', 'jurado.tn@uninavarra.edu.co'), $noVideo, 0)->assertSessionHasNoErrors();
        $this->artisan('catedra:jurados-prueba', ['--limpiar' => true, '--forzar' => true])->assertSuccessful();
        $this->assertNull($noVideo->fresh()->video_url);
        $this->assertDatabaseCount('evaluations', 0);
    }

    public function test_grade_weights_must_add_up_to_100(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.editions.update', $this->edition), [
            'name' => $this->edition->name, 'year' => 2026, 'community_weight' => 30, 'jury_weight' => 50, 'expert_score_weight' => 30, 'committee_mode' => 'separado',
            'vote_grade_mode' => 'relativo', 'max_votes_per_user' => 1, 'finalists_count' => 5, 'expert_title' => 'Profesor experto',
        ])->assertSessionHasErrors('community_weight');
    }

    public function test_changing_the_rubric_keeps_scores_and_recalculates_totals(): void
    {
        $venture = $this->venture('Gamma');
        $this->evaluate($this->jury(), $venture, 5);
        $criteria = $this->edition->criteria()->get();

        $admin = User::factory()->create(['role' => 'admin']);
        $rows = $criteria->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'weight' => 20, 'max_score' => 5])->values()->all();
        $rows[] = ['name' => 'Pitch', 'weight' => 20, 'max_score' => 5]; // criterio nuevo sin calificar

        $this->actingAs($admin)->put(route('admin.editions.update', $this->edition), [
            'name' => $this->edition->name, 'year' => 2026, 'community_weight' => 30, 'max_votes_per_user' => 1,
            'finalists_count' => 5, 'expert_title' => 'Mentor senior', 'jury_weight' => 40, 'expert_score_weight' => 30, 'committee_mode' => 'separado', 'vote_grade_mode' => 'relativo', 'is_published' => 1,
            'criteria' => $rows,
        ])->assertSessionHasNoErrors();

        $this->assertSame(6, $this->edition->criteria()->count());
        $this->assertSame(5, Evaluation::first()->scores()->count());          // los puntajes previos se conservan
        $this->assertEquals(83.33, (float) Evaluation::first()->total_score);  // 5 criterios a 100%, el nuevo a 0%
        $this->assertSame('Mentor senior', $this->edition->fresh()->expert_title);
    }

    private const REVIEW = ['rating' => 4, 'body' => 'Muy buena idea, el pitch es claro.'];

    public function test_each_person_has_a_single_vote(): void
    {
        $voter = User::factory()->create();
        $a = $this->venture('Uno');
        $b = $this->venture('Dos');

        $this->actingAs($voter)->postJson(route('ventures.vote', $a), self::REVIEW)->assertOk()->assertJson(['voted' => true]);
        $this->actingAs($voter)->postJson(route('ventures.vote', $b), self::REVIEW)->assertStatus(422)->assertJsonFragment(['voted' => false]);
        $this->actingAs($voter)->postJson(route('ventures.vote', $a))->assertJson(['voted' => false]); // retira el voto (sin pedir nada)
        $this->actingAs($voter)->postJson(route('ventures.vote', $b), self::REVIEW)->assertJson(['voted' => true]);  // y lo cambia
        $this->assertSame(1, $voter->votes()->count());
    }

    /** Votar es un paso a paso: estrellas y comentario son obligatorios y se guardan con el voto. */
    public function test_voting_requires_stars_and_a_comment(): void
    {
        $voter = User::factory()->create();
        $venture = $this->venture('Paso a paso');

        $this->actingAs($voter)->postJson(route('ventures.vote', $venture))->assertStatus(422);
        $this->actingAs($voter)->postJson(route('ventures.vote', $venture), ['rating' => 5])->assertStatus(422);
        $this->actingAs($voter)->postJson(route('ventures.vote', $venture), ['rating' => 5, 'body' => 'Corto'])->assertStatus(422);
        $this->assertDatabaseCount('votes', 0);
        $this->assertDatabaseCount('comments', 0);

        $this->actingAs($voter)->postJson(route('ventures.vote', $venture), self::REVIEW)->assertOk()->assertJson(['voted' => true]);
        $this->assertDatabaseHas('comments', ['venture_id' => $venture->id, 'user_id' => $voter->id, 'rating' => 4]);

        // Retirar y volver a votar actualiza la misma opinión en lugar de duplicarla
        $this->actingAs($voter)->postJson(route('ventures.vote', $venture));
        $this->actingAs($voter)->postJson(route('ventures.vote', $venture), ['rating' => 5, 'body' => 'Lo pensé mejor: excelente equipo.'])->assertOk();
        $this->assertSame(1, $venture->comments()->count());
        $this->assertSame(5, (int) $venture->comments()->first()->rating);
    }

    public function test_juries_cannot_vote_as_community(): void
    {
        $this->actingAs($this->jury())->postJson(route('ventures.vote', $this->venture('Delta')))->assertStatus(422);
        $this->assertDatabaseCount('votes', 0);
    }

    public function test_unverified_users_cannot_pledge(): void
    {
        $venture = $this->venture('Epsilon');
        $this->actingAs(User::factory()->unverified()->create(['role' => 'inversionista']))
            ->post(route('ventures.pledge', $venture), ['types' => ['inversion'], 'amount' => 1000, 'message' => 'Quiero invertir en esto']);
        $this->assertDatabaseCount('support_pledges', 0);
    }

    public function test_investor_ranking_only_counts_completed_pledges_of_people_who_consented(): void
    {
        $venture = $this->venture('Zeta');
        $public = User::factory()->create(['role' => 'inversionista', 'organization' => 'Fondo Visible']);
        $private = User::factory()->create(['role' => 'inversionista']);

        $this->actingAs($public)->post(route('ventures.pledge', $venture), ['types' => ['mercadeo', 'inversion'], 'amount' => 5000000, 'message' => 'Quiero invertir en esto', 'public_ranking' => 1]);
        $this->actingAs($private)->post(route('ventures.pledge', $venture), ['types' => ['donacion'], 'amount' => 9000000, 'message' => 'Quiero donar a este equipo']);

        // Mientras sean solo intenciones, nadie aparece en el ranking
        $this->assertCount(0, InvestorRanking::for($this->edition));

        SupportPledge::query()->update(['status' => 'concretado']);
        $ranking = InvestorRanking::for($this->edition);
        $this->assertCount(1, $ranking);
        $this->assertEquals(5000000, (float) $ranking->first()->total_amount);
        $this->assertSame(1, InvestorRanking::hiddenCount($this->edition));
        $this->get(route('ranking', ['vista' => 'inversionistas']))->assertOk()->assertSee('Fondo Visible')->assertSee('centroemprendimiento@uninavarra.edu.co');
    }

    public function test_a_pledge_can_offer_several_kinds_of_support(): void
    {
        $venture = $this->venture('Eta');
        $ally = User::factory()->create(['role' => 'inversionista']);

        $this->actingAs($ally)->post(route('ventures.pledge', $venture), ['types' => [], 'message' => 'Quiero ayudar al equipo'])->assertSessionHasErrors('types');
        $this->actingAs($ally)->post(route('ventures.pledge', $venture), ['types' => ['nave_espacial'], 'message' => 'Quiero ayudar al equipo'])->assertSessionHasErrors();

        $this->actingAs($ally)->post(route('ventures.pledge', $venture), [
            'types' => ['mercadeo', 'equipos', 'herramientas'], 'amount' => 3000000, 'message' => 'Les presto equipos y los difundo en redes',
        ])->assertSessionHasNoErrors();

        $pledge = SupportPledge::first();
        $this->assertSame(['equipos', 'herramientas', 'mercadeo'], $pledge->types); // en el orden de TYPES
        $this->assertSame('equipos', $pledge->type);
        $this->assertNull($pledge->amount); // sin inversión ni donación no se guarda monto
        $this->assertSame('Equipos · Herramientas · Mercadeo', $pledge->type_label);
    }

    public function test_investor_page_is_the_funnel_destination(): void
    {
        $this->venture('Theta');
        $this->get(route('investors'))->assertOk()->assertSee('¿Qué puedes aportar?')->assertSee('Materia prima')->assertSee('Theta');
        $this->get('/')->assertOk()->assertSee(route('investors'));
    }

    /** Votación en redes: se deshabilita el voto en la página y el admin registra los votos a mano. */
    public function test_admin_can_switch_to_manual_votes_from_social_networks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $voter = User::factory()->create();
        $a = $this->venture('Redes Uno');
        $b = $this->venture('Redes Dos');
        $b->update(['video_url' => 'https://www.youtube.com/shorts/8g3BoGJWKXM']);

        $this->actingAs($admin)->post(route('admin.results.web-voting', $this->edition))->assertRedirect();
        $this->assertFalse($this->edition->fresh()->hasWebVoting());
        $this->assertSame('redes', $this->edition->fresh()->votingStatus());

        // Ya no se puede votar en la página; el botón lleva al video
        $this->actingAs($voter)->postJson(route('ventures.vote', $a), self::REVIEW)->assertStatus(422);
        $this->assertDatabaseCount('votes', 0);
        $this->get(route('ventures.show', $b))->assertOk()->assertSee('Votar en YouTube')->assertSee('Ver en pantalla completa');

        // El admin escribe los votos de las redes en la tabla de resultados
        $this->actingAs($admin)->get(route('admin.results.index'))->assertOk()->assertSee('rows['.$a->id.'][external_votes]', false);
        $this->actingAs($admin)->put(route('admin.results.update', $this->edition), ['rows' => [
            $a->id => ['external_votes' => 120], $b->id => ['external_votes' => 30],
        ]])->assertRedirect();

        $ranking = Ranking::for($this->edition)->keyBy('name');
        $this->assertSame(120, $ranking['Redes Uno']->votes_count);
        $this->assertEquals(5.0, $ranking['Redes Uno']->community_grade); // el más votado = 5 (modo relativo)
        $this->assertEquals(2.0, $ranking['Redes Dos']->community_grade);
        $this->assertSame(['Redes Uno', 'Redes Dos'], Venture::orderByVotes()->pluck('name')->all());

        // Al volver a habilitar la página, los votos registrados se conservan
        $this->actingAs($admin)->post(route('admin.results.web-voting', $this->edition));
        $this->actingAs($admin)->put(route('admin.results.update', $this->edition), ['rows' => [$a->id => ['final_position' => 1]]]);
        $this->assertSame(120, $a->fresh()->external_votes);
    }

    /** Resultados: una columna por jurado y por experto con su nota, antes de la comunidad. */
    /** La tabla de votos (likes) → nota de la comunidad se ve en Resultados. */
    public function test_results_show_the_vote_ranges(): void
    {
        $this->edition->update(['vote_grade_mode' => 'rangos', 'vote_grade_thresholds' => null]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.results.index'))
            ->assertOk()->assertSeeInOrder(['0 a 14 votos', '3', '15 a 49 votos', '3,5', '198 o más votos', '5'])->assertSee('Cambiar rangos');
    }

    public function test_results_show_each_jury_member_grade(): void
    {
        $venture = $this->venture('Columnas');
        $jury = $this->jury();
        $jury->update(['name' => 'Ana Jurado']);
        $expert = $this->jury(true);
        $expert->update(['name' => 'Pedro Experto']);
        $this->evaluate($jury, $venture, 4);
        $this->evaluate($expert, $venture, 3);

        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.results.index'))
            ->assertOk()
            ->assertSeeInOrder(['Ana Jurado', 'Pedro Experto', 'Jurados', 'Votos', 'Comunidad', 'Nota final'])
            ->assertSeeInOrder(['Columnas', '4,00', '3,00']);
    }

    public function test_excel_export_is_ordered_with_the_full_rubric(): void
    {
        $venture = $this->venture('Export');
        $this->venture('Sin evaluar');
        $jury = $this->jury();
        $jury->update(['name' => 'Ana Jurado']);
        $this->evaluate($jury, $venture, [4, 3.5, 5, 0, 2]);

        $content = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.results.export', $this->edition))->assertOk()->streamedContent();
        $file = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($file, $content);
        $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        unlink($file);

        $this->assertSame(['Resultados', 'Rúbrica por jurado', 'Comentarios'], $book->getSheetNames());

        // Resultados: nota de cada jurado antes del comité, luego votos, comunidad y nota final
        $summary = $book->getSheet(0)->rangeToArray('A4:N5', null, false, false);
        $this->assertSame("Jurado\nAna Jurado", $summary[0][4]);
        $this->assertSame(['Export', 2.9], [$summary[1][1], $summary[1][4]]);
        $header = $summary[0];
        $this->assertLessThan(array_search('Nota final', $header), array_search("Comunidad\n30%", $header));

        // Rúbrica: el evaluador arriba y debajo cada criterio con su puntaje y su nota
        $rubric = $book->getSheet(1);
        $first = $this->edition->criteria->first();
        $this->assertSame('Jurado: Ana Jurado', $rubric->getCell('D4')->getValue());
        $this->assertSame("{$first->name}\n(máx. 5)", $rubric->getCell('D5')->getValue());
        $row = $rubric->rangeToArray('A6:I6', null, false, false)[0];
        $this->assertSame(['Export', 4.0, 3.5, 5.0, 0.0, 2.0, 2.9], [$row[1], ...array_map('floatval', array_slice($row, 3, 6))]);
        $this->assertNull($rubric->getCell('D7')->getValue()); // «Sin evaluar»: celdas vacías

        $this->assertSame([['Export', null, 'Jurado: Ana Jurado', 2.9, 'ok']], $book->getSheet(2)->rangeToArray('A5:E5', null, false, false));
    }

    public function test_admin_can_delete_test_evaluations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $jury = $this->jury();
        $a = $this->venture('Prueba A');
        $b = $this->venture('Prueba B');
        $this->evaluate($jury, $a, 4);
        $this->evaluate($jury, $b, 3);

        $this->actingAs($jury)->delete(route('admin.results.evaluations.destroy', [$this->edition, Evaluation::first()]))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.results.evaluations', $this->edition))->assertOk()->assertSee('Borrar todas');
        $this->actingAs($admin)->delete(route('admin.results.evaluations.destroy', [$this->edition, Evaluation::first()]))->assertRedirect();
        $this->assertSame(1, Evaluation::count());

        $this->actingAs($admin)->delete(route('admin.results.evaluations.destroy-jury', [$this->edition, $jury]))->assertRedirect();
        $this->assertSame(0, Evaluation::count());
        $this->assertNotNull($jury->fresh()); // la cuenta del jurado se conserva
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
