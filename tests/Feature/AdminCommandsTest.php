<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Edition;
use App\Models\User;
use App\Models\Venture;
use App\Models\Vote;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
