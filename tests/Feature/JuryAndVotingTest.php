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
            'community_weight' => 30, 'max_votes_per_user' => 1, 'expert_title' => 'Profesor experto', 'expert_weight' => 2,
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
        $this->assertEquals(80.0, (float) $evaluation->total_score); // 4/5 en todos los criterios
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

    public function test_expert_jury_has_more_weight_in_the_committee_score(): void
    {
        $venture = $this->venture('Agro Tres');
        $this->evaluate($this->jury(expert: true), $venture, 5); // 100 puntos, pesa ×2
        $this->evaluate($this->jury(), $venture, 2);              // 40 puntos, pesa ×1

        // (100×2 + 40×1) / 3 = 80
        $this->assertEquals(80.0, JuryScoring::forEdition($this->edition)->get($venture->id)['score']);
    }

    public function test_final_ranking_combines_committee_and_community(): void
    {
        $a = $this->venture('Alfa');
        $b = $this->venture('Beta');
        $this->evaluate($this->jury(), $a, 5); // comité 100
        $this->evaluate($this->jury(), $b, 5);
        $a->votes()->create(['user_id' => User::factory()->create()->id, 'edition_id' => $this->edition->id]);

        $ranking = Ranking::for($this->edition)->keyBy('slug');
        $this->assertEquals(100.0, $ranking['alfa']->final_score); // 100×0,7 + 100×0,3
        $this->assertEquals(70.0, $ranking['beta']->final_score);  // 100×0,7 + 0×0,3
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
            'finalists_count' => 5, 'expert_title' => 'Mentor senior', 'expert_weight' => 3, 'is_published' => 1,
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

    public function test_investor_ranking_only_shows_people_who_consented(): void
    {
        $venture = $this->venture('Zeta');
        $public = User::factory()->create(['role' => 'inversionista', 'organization' => 'Fondo Visible']);
        $private = User::factory()->create(['role' => 'inversionista']);

        $this->actingAs($public)->post(route('ventures.pledge', $venture), ['type' => 'inversion', 'amount' => 5000000, 'message' => 'Quiero invertir en esto', 'public_ranking' => 1]);
        $this->actingAs($private)->post(route('ventures.pledge', $venture), ['type' => 'donacion', 'amount' => 9000000, 'message' => 'Quiero donar a este equipo']);

        $ranking = InvestorRanking::for($this->edition);
        $this->assertCount(1, $ranking);
        $this->assertEquals(5000000, (float) $ranking->first()->total_amount);
        $this->assertSame(1, InvestorRanking::hiddenCount($this->edition));
        $this->get(route('ranking', ['vista' => 'inversionistas']))->assertOk()->assertSee('Fondo Visible');
        $this->assertSame(2, SupportPledge::count());
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
