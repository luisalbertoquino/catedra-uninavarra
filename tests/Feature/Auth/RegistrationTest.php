<?php

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\PassesBotProtection;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use PassesBotProtection, RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Usuario Prueba',
            'email' => 'usuario@example.com',
            'role' => 'comunidad',
            'password' => 'Clave12345',
            'password_confirmation' => 'Clave12345',
            'terms' => '1',
        ], $this->humanFields(), $overrides);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk()->assertSee('name="_hp_ts"', false);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', $this->validData());

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
        $this->assertDatabaseHas('users', ['email' => 'usuario@example.com', 'role' => 'comunidad']);
    }

    public function test_bots_that_fill_the_honeypot_are_rejected(): void
    {
        $this->post('/register', $this->validData(['website' => 'http://spam.example']))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_forms_submitted_too_fast_are_rejected(): void
    {
        $this->post('/register', $this->validData($this->humanFields(0)))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_forms_without_timestamp_are_rejected(): void
    {
        $this->post('/register', $this->validData(['_hp_ts' => 'manipulado']))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_disposable_emails_are_rejected(): void
    {
        $this->post('/register', $this->validData(['email' => 'bot@yopmail.com']))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_passwords_need_letters_and_numbers(): void
    {
        $this->post('/register', $this->validData(['password' => 'soloLetras', 'password_confirmation' => 'soloLetras']))
            ->assertSessionHasErrors('password');
    }

    public function test_cannot_self_register_as_admin_or_jury(): void
    {
        $this->post('/register', $this->validData(['role' => 'admin']))->assertSessionHasErrors('role');
        $this->post('/register', $this->validData(['role' => 'jurado', 'email' => 'otro@example.com']))->assertSessionHasErrors('role');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_is_rate_limited_per_ip(): void
    {
        foreach (range(1, 3) as $i) {
            $this->post('/register', $this->validData(['email' => "u{$i}@example.com"]));
            auth()->logout();
        }

        $this->post('/register', $this->validData(['email' => 'u4@example.com']))->assertStatus(429);
    }
}
