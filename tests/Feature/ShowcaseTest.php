<?php

namespace Tests\Feature;

use App\Models\InvestorProfile;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShowcaseTest extends TestCase
{
    use RefreshDatabase;

    /** La migración carga los inversionistas y los portafolios entregados por la Cátedra. */
    public function test_public_pages_show_investors_and_portfolios(): void
    {
        $this->get(route('investor-profiles'))->assertOk()
            ->assertSeeInOrder(['Alexander Quintero Bonilla', 'Dairo Andrés Rivera', 'Juan Diego Durán Hernández'])
            ->assertSee('images/inversionistas/dairo-andres-rivera.webp')
            ->assertSee('Ver trayectoria completa');

        $this->get(route('portfolios'))->assertOk()
            ->assertSeeInOrder(['Portafolio LION HUB 2026-2', 'Portafolio LION HUB 2026-1'])
            ->assertSee('https://uninavarra.edu.co/portafolio-lion-hub-2026-2/')
            ->assertSee(asset('portafolio-lion-2-3.pdf'));

        // Botón flotante con el PDF del portafolio actual y sección en el inicio
        $this->get('/')->assertOk()->assertSee('Quiénes escuchan los pitch')->assertSee('Portafolio')->assertSee(asset('portafolio-lion-2-3.pdf'));
    }

    public function test_hidden_profiles_and_portfolios_are_not_public(): void
    {
        InvestorProfile::where('name', 'Dairo Andrés Rivera')->update(['is_published' => false]);
        Portfolio::query()->update(['is_published' => false]);

        $this->get(route('investor-profiles'))->assertOk()->assertDontSee('Dairo Andrés Rivera');
        $this->get('/')->assertOk()->assertDontSee(asset('portafolio-lion-2-3.pdf'));
    }

    public function test_admin_manages_investor_profiles(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.investor-profiles.store'), [
            'name' => 'Ana Inversora', 'headline' => 'Fondo semilla', 'expertise' => 'Capital semilla, Fintech',
            'bio' => "Primer párrafo.\nSegundo párrafo.", 'sort' => 4, 'is_published' => 1,
            'photo' => UploadedFile::fake()->image('ana.jpg', 800, 600),
        ])->assertRedirect(route('admin.investor-profiles.index'));

        $ana = InvestorProfile::where('name', 'Ana Inversora')->firstOrFail();
        Storage::disk('public')->assertExists($ana->photo);
        $this->assertSame(['Capital semilla', 'Fintech'], $ana->expertise_list);
        $this->get(route('investor-profiles'))->assertSee('Ana Inversora')->assertSee('Segundo párrafo.');

        // Las fotos que vienen en public/ no se borran al eliminar el perfil
        $seeded = InvestorProfile::where('name', 'Juan Diego Durán Hernández')->firstOrFail();
        $this->actingAs($admin)->delete(route('admin.investor-profiles.destroy', $seeded))->assertRedirect();
        $this->assertFileExists(public_path('images/inversionistas/juan-diego-duran-hernandez.webp'));

        $this->actingAs(User::factory()->create(['role' => 'jurado']))->get(route('admin.investor-profiles.index'))->assertForbidden();
    }

    public function test_admin_manages_portfolios_with_a_single_floating_button(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.portfolios.store'), ['title' => 'Sin enlace'])->assertSessionHasErrors('url');

        $this->actingAs($admin)->post(route('admin.portfolios.store'), [
            'title' => 'Portafolio LION HUB 2027-1', 'period' => '2027-1', 'is_published' => 1, 'is_featured' => 1,
            'pdf_file' => UploadedFile::fake()->create('portafolio.pdf', 500, 'application/pdf'),
        ])->assertRedirect(route('admin.portfolios.index'));

        $new = Portfolio::where('period', '2027-1')->firstOrFail();
        Storage::disk('public')->assertExists($new->pdf);
        $this->assertSame(1, Portfolio::where('is_featured', true)->count());
        $this->assertTrue(Portfolio::featured()->is($new));
        $this->get('/')->assertSee($new->pdf_url);
    }
}
