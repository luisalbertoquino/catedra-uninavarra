<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google.client_id' => 'test-id', 'services.google.client_secret' => 'test-secret']);
    }

    private function fakeGoogle(string $email, bool $verified = true, string $id = 'g-123'): void
    {
        $user = (new GoogleUser)->setRaw(['email_verified' => $verified])->map(['id' => $id, 'name' => 'Ana Pérez', 'email' => $email]);
        $provider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($user);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_button_only_shows_when_configured(): void
    {
        $this->get('/login')->assertSee('Continuar con Google');
        config(['services.google.client_id' => null]);
        $this->get('/login')->assertDontSee('Continuar con Google');
        $this->get('/auth/google')->assertNotFound();
    }

    public function test_existing_account_logs_in_and_gets_verified(): void
    {
        $jury = User::factory()->unverified()->create(['email' => 'ana@uninavarra.edu.co', 'role' => 'jurado']);
        $this->fakeGoogle('Ana@uninavarra.edu.co');

        $this->get('/auth/google/callback')->assertRedirect(route('jury.index'));
        $this->assertAuthenticatedAs($jury);
        $this->assertNotNull($jury->fresh()->email_verified_at);
        $this->assertSame('g-123', $jury->fresh()->google_id);
    }

    public function test_new_account_must_accept_terms_before_it_is_created(): void
    {
        $this->fakeGoogle('nuevo@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect(route('google.complete'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'nuevo@gmail.com']);
        $this->get(route('google.complete'))->assertOk()->assertSee('nuevo@gmail.com');

        $this->post(route('google.complete'), ['name' => 'Ana Pérez', 'role' => 'comunidad'])->assertSessionHasErrors('terms');
        $this->post(route('google.complete'), ['name' => 'Ana Pérez', 'role' => 'inversionista', 'terms' => '1'])->assertSessionHasErrors('phone');

        $this->post(route('google.complete'), ['name' => 'Ana Pérez', 'role' => 'comunidad', 'terms' => '1'])->assertRedirect('/mi-cuenta');
        $user = User::where('email', 'nuevo@gmail.com')->first();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('comunidad', $user->role);
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $this->fakeGoogle('raro@gmail.com', verified: false);

        $this->get('/auth/google/callback')->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_complete_without_google_session_goes_to_register(): void
    {
        $this->get(route('google.complete'))->assertRedirect(route('register'));
        $this->post(route('google.complete'), ['name' => 'X', 'role' => 'comunidad', 'terms' => '1'])->assertRedirect(route('register'));
    }
}
