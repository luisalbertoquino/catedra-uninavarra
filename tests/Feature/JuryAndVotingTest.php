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
            'community_weight' => 30, 'jury_weight' => 40, 'expert_score_weight' => 30, 'max_votes_per_user' => 1, 'expert_title' => 'Profesor experto',
            'is_active' => true, 'is_published' => true,
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

    /** Envía una evaluación con el mismo puntaje en todos los criterios. */
    private function evaluate(User $jury, Venture $venture, int $score)
    {
        $scores = $this->edition->criteria->mapWithKeys(fn ($c) => [$c->id => $score])->all();

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

    public function test_vote_grade_by_ranges_is_configurable(): void
    {
        $this->edition->update(['vote_grade_mode' => 'rangos', 'vote_grade_thresholds' => ['2' => 2, '3' => 4, '4' => 6, '5' => 8]]);

        $this->assertEquals(1.0, $this->edition->voteGrade(1, 100));
        $this->assertEquals(3.0, $this->edition->voteGrade(5, 100));
        $this->assertEquals(5.0, $this->edition->voteGrade(20, 100));

        $this->edition->update(['vote_grade_mode' => 'relativo']);
        $this->assertEquals(3.0, $this->edition->fresh()->voteGrade(5, 10)); // 1 + 4 × 5/10
    }

    public function test_grade_weights_must_add_up_to_100(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.editions.update', $this->edition), [
            'name' => $this->edition->name, 'year' => 2026, 'community_weight' => 30, 'jury_weight' => 50, 'expert_score_weight' => 30,
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
            'finalists_count' => 5, 'expert_title' => 'Mentor senior', 'jury_weight' => 40, 'expert_score_weight' => 30, 'vote_grade_mode' => 'relativo', 'is_published' => 1,
            'criteria' => $rows,
        ])->assertSessionHasNoErrors();

        $this->assertSame(6, $this->edition->criteria()->count());
        $this->assertSame(5, Evaluation::first()->scores()->count());          // los puntajes previos se conservan
        $this->assertEquals(83.33, (float) Evaluation::first()->total_score);  // 5 criterios a 100%, el nuevo a 0%
        $this->assertSame('Mentor senior', $this->edition->fresh()->expert_title);
    }

    public function test_each_person_has_a_single_vote(): void
    {
        $voter = User::factory()->create();
        $a = $this->venture('Uno');
        $b = $this->venture('Dos');

        $this->actingAs($voter)->postJson(route('ventures.vote', $a))->assertOk()->assertJson(['voted' => true]);
        $this->actingAs($voter)->postJson(route('ventures.vote', $b))->assertStatus(422)->assertJsonFragment(['voted' => false]);
        $this->actingAs($voter)->postJson(route('ventures.vote', $a))->assertJson(['voted' => false]); // retira el voto
        $this->actingAs($voter)->postJson(route('ventures.vote', $b))->assertJson(['voted' => true]);  // y lo cambia
        $this->assertSame(1, $voter->votes()->count());
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
            ->post(route('ventures.pledge', $venture), ['type' => 'inversion', 'amount' => 1000, 'message' => 'Quiero invertir en esto']);
        $this->assertDatabaseCount('support_pledges', 0);
    }

    public function test_investor_ranking_only_counts_completed_pledges_of_people_who_consented(): void
    {
        $venture = $this->venture('Zeta');
        $public = User::factory()->create(['role' => 'inversionista', 'organization' => 'Fondo Visible']);
        $private = User::factory()->create(['role' => 'inversionista']);

        $this->actingAs($public)->post(route('ventures.pledge', $venture), ['type' => 'inversion', 'amount' => 5000000, 'message' => 'Quiero invertir en esto', 'public_ranking' => 1]);
        $this->actingAs($private)->post(route('ventures.pledge', $venture), ['type' => 'donacion', 'amount' => 9000000, 'message' => 'Quiero donar a este equipo']);

        // Mientras sean solo intenciones, nadie aparece en el ranking
        $this->assertCount(0, InvestorRanking::for($this->edition));

        SupportPledge::query()->update(['status' => 'concretado']);
        $ranking = InvestorRanking::for($this->edition);
        $this->assertCount(1, $ranking);
        $this->assertEquals(5000000, (float) $ranking->first()->total_amount);
        $this->assertSame(1, InvestorRanking::hiddenCount($this->edition));
        $this->get(route('ranking', ['vista' => 'inversionistas']))->assertOk()->assertSee('Fondo Visible')->assertSee('centroemprendimiento@uninavarra.edu.co');
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
