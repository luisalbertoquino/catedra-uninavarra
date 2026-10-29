<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Edition;
use App\Models\Setting;
use App\Models\User;
use App\Models\Venture;
use App\Models\Vote;
use App\Notifications\WelcomeInvitation;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_admin_sends_password_link_and_replaces_old_admin(): void
    {
        Notification::fake();
        $old = User::factory()->create(['email' => 'admin@uninavarra.edu.co', 'role' => 'admin']);

        $this->artisan('catedra:crear-admin', ['email' => 'webmaster@uninavarra.edu.co', '--nombre' => 'Webmaster UNINAVARRA',
            '--enviar-enlace' => true, '--reemplaza' => 'admin@uninavarra.edu.co'])->assertSuccessful();

        $new = User::where('email', 'webmaster@uninavarra.edu.co')->first();
        $this->assertTrue($new->isAdmin());
        $this->assertNotNull($new->email_verified_at);
        Notification::assertSentTo($new, ResetPassword::class);
        $this->assertModelMissing($old);
    }

    public function test_create_admin_refuses_to_replace_non_admin(): void
    {
        $user = User::factory()->create(['role' => 'comunidad']);

        $this->artisan('catedra:crear-admin', ['email' => 'nuevo@uninavarra.edu.co', '--enviar-enlace' => true, '--reemplaza' => $user->email])
            ->assertFailed();
        $this->assertModelExists($user);
        $this->assertDatabaseMissing('users', ['email' => 'nuevo@uninavarra.edu.co']);
    }

    public function test_delete_votes_of_current_edition(): void
    {
        $edition = Edition::create(['name' => 'Edición de prueba', 'slug' => 'edicion-prueba', 'year' => 2026, 'period' => 2,
            'is_active' => true, 'is_published' => true]);
        $venture = Venture::create(['edition_id' => $edition->id, 'name' => 'Prueba', 'slug' => 'prueba', 'is_published' => true]);
        $user = User::factory()->create();
        Vote::create(['user_id' => $user->id, 'venture_id' => $venture->id, 'edition_id' => $edition->id]);
        Comment::create(['user_id' => $user->id, 'venture_id' => $venture->id, 'body' => 'Comentario de prueba', 'rating' => 5]);

        $this->artisan('catedra:borrar-votos', ['--forzar' => true])->assertSuccessful();
        $this->assertSame(0, Vote::count());
        $this->assertSame(1, Comment::count());

        $this->artisan('catedra:borrar-votos', ['--forzar' => true, '--con-comentarios' => true])->assertSuccessful();
        $this->assertSame(0, Comment::count());
    }

    public function test_delete_demo_users_keeps_real_accounts_and_staff(): void
    {
        $demo = User::factory()->create(['email' => 'estudiante7@uninavarra.edu.co', 'role' => 'comunidad']);
        $investor = User::factory()->create(['email' => 'inversionista@demo.co', 'role' => 'inversionista']);
        $extra = User::factory()->create(['email' => 'prueba@gmail.com', 'role' => 'comunidad']);
        $real = User::factory()->create(['email' => 'maria.perez@uninavarra.edu.co', 'role' => 'comunidad']);
        $jury = User::factory()->create(['email' => 'estudiante9@uninavarra.edu.co', 'role' => 'jurado']);

        $this->artisan('catedra:borrar-usuarios-demo', ['--correo' => ['Prueba@gmail.com'], '--forzar' => true])->assertSuccessful();

        $this->assertModelMissing($demo);
        $this->assertModelMissing($investor);
        $this->assertModelMissing($extra);
        $this->assertModelExists($real);
        $this->assertModelExists($jury);
    }

    public function test_admin_invites_jury_who_sets_password_once(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.juries.store'), ['name' => 'Ana Jurado', 'email' => 'ana@uninavarra.edu.co', 'invite' => '1'])
            ->assertSessionMissing('credentials');
        $jury = User::where('email', 'ana@uninavarra.edu.co')->first();
        Notification::assertSentTo($jury, WelcomeInvitation::class);

        auth()->logout();
        $url = WelcomeInvitation::url($jury);
        $this->get($url)->assertOk()->assertSee('ana@uninavarra.edu.co');
        $this->post($url, ['password' => 'NuevaClave2026', 'password_confirmation' => 'NuevaClave2026'])->assertRedirect(route('jury.index'));
        $this->assertTrue(Hash::check('NuevaClave2026', $jury->fresh()->password));

        auth()->logout();
        $this->get($url)->assertForbidden(); // el enlace ya se usó
    }

    public function test_invitations_are_only_for_staff(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'comunidad']);

        $this->actingAs($admin)->post(route('admin.users.invite', $user))->assertSessionHas('error');
        $this->actingAs($admin)->post(route('admin.users.invite', $admin))->assertSessionHas('status');
        Notification::assertNotSentTo($user, WelcomeInvitation::class);
        Notification::assertSentTo($admin, WelcomeInvitation::class);

        auth()->logout();
        $this->get(WelcomeInvitation::url($user))->assertForbidden();
    }

    public function test_admin_ventures_table_sorts_by_group_number(): void
    {
        $edition = Edition::create(['name' => 'Edición de prueba', 'slug' => 'edicion-prueba', 'year' => 2026, 'period' => 2,
            'is_active' => true, 'is_published' => true]);
        foreach (['Grupo 12' => 'Doce', 'Grupo 2' => 'Dos', 'Grupo 7' => 'Siete'] as $group => $name) {
            Venture::create(['edition_id' => $edition->id, 'name' => $name, 'slug' => strtolower($name), 'team_group' => $group, 'is_published' => true]);
        }
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.ventures.index', ['orden' => 'grupo', 'dir' => 'asc']))->assertSeeInOrder(['>Dos<', '>Siete<', '>Doce<'], false);
        $this->actingAs($admin)->get(route('admin.ventures.index', ['orden' => 'grupo', 'dir' => 'desc']))->assertSeeInOrder(['>Doce<', '>Siete<', '>Dos<'], false);
    }

    public function test_only_the_webmaster_can_delete_users(): void
    {
        $webmaster = User::factory()->create(['email' => 'webmaster@uninavarra.edu.co', 'role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $victim = User::factory()->create(['role' => 'comunidad']);

        $this->actingAs($otherAdmin)->delete(route('admin.users.destroy', $victim))->assertForbidden();
        $this->actingAs($otherAdmin)->get(route('admin.users.index'))->assertDontSee('Borrar cuenta');
        $this->assertModelExists($victim);

        $this->actingAs($webmaster)->get(route('admin.users.index'))->assertSee('Borrar cuenta');
        $this->actingAs($webmaster)->delete(route('admin.users.destroy', $victim))->assertSessionHas('status');
        $this->assertModelMissing($victim);

        $this->actingAs($webmaster)->delete(route('admin.users.destroy', $webmaster))->assertSessionHas('error');
        $this->assertModelExists($webmaster);
    }

    public function test_welcome_message_text_is_editable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Setting::put(['welcome_jury' => "Hola {nombre}, serás **{rol}** en {evento}.\nGracias por acompañarnos."]);
        $jury = User::factory()->create(['name' => 'Ana Pérez', 'role' => 'jurado']);

        $html = (string) (new WelcomeInvitation)->toMail($jury)->render();
        $this->assertMatchesRegularExpression('#Hola Ana Pérez, serás <strong[^>]*>jurado</strong>#u', $html);
        $this->assertStringContainsString('Gracias por acompañarnos.', $html);
        $this->assertStringContainsString('Crear mi contraseña', $html);

        $this->actingAs($admin)->get(route('admin.settings.welcome', ['tipo' => 'jurado']))->assertOk()->assertSee('Gracias por acompañarnos.');
    }
}
