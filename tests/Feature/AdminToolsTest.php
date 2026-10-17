<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Setting;
use App\Models\User;
use App\Services\PortfolioImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AdminToolsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Edition $edition;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->edition = Edition::create([
            'name' => 'Cátedra UNINAVARRA 2026-2', 'slug' => 'catedra-2026-2', 'year' => 2026, 'period' => 2,
            'voting_starts_at' => '2026-10-01 00:00', 'voting_ends_at' => '2026-11-06 23:59', 'final_at' => '2026-11-07 10:00',
            'is_active' => true, 'is_published' => true,
        ]);
        $this->edition->milestones()->create(['title' => 'Congreso', 'starts_at' => '2026-08-21', 'sort' => 0]);
        $this->edition->criteria()->create(['name' => 'Innovación', 'weight' => 100, 'max_score' => 5, 'sort' => 0]);
    }

    /** Excel con el mismo formato del portafolio: un bloque por grupo, integrantes debajo y un bloque duplicado. */
    private function portfolioExcel(): string
    {
        $sheet = (new Spreadsheet())->getActiveSheet();
        $sheet->fromArray([
            ['Nombre', 'Programa', 'Grupo', 'Nombre de la Idea de Negocio', 'DESCRIPCIÓN DE LA IDEA', 'POBLACIÓN OBJETIVO', 'MISIÓN', 'VISIÓN', 'MONTO ESTIMADO PARA INICIAR EL EMPRENDIMIENTO ', 'MONTO QUE SOLICITARIA A UN INVERSIONISTA ', 'LOGO'],
            ['ana pérez gómez', 'Enfermeria', 'Grupo 1', 'BioCura Ancestral', 'Parches curativos naturales. Hechos en el Huila.', 'Adultos de Neiva', 'Curar heridas', 'Ser líderes en 2029', '$48.100.000,00 COP', '6000000', null],
            ['Luis Gómez', 'Enfermeria', null, null, null, null, null, null, null, null, null],
            [null, null, null, null, null, null, null, null, null, null, null],
            ['Grupo sin datos', null, 'Grupo 2', null, null, null, null, null, null, null, null],
            ['Sara Ruiz, Juan Díaz', 'Medicina', 'grupo 3', 'Rayvix', 'Apoyo a imágenes diagnósticas', 'IPS', 'Misión R', 'Visión R', '$6,000,000', '$ 2.000.000', null],
            ['ana pérez gómez', 'Enfermeria', 'Grupo 1', 'BioCura Ancestral', 'Duplicado', null, null, null, null, null, null],
        ]);
        $path = tempnam(sys_get_temp_dir(), 'portafolio').'.xlsx';
        (new Xlsx($sheet->getParent()))->save($path);

        return $path;
    }

    public function test_the_importer_reads_the_portfolio_format(): void
    {
        $ventures = app(PortfolioImporter::class)->parse($this->portfolioExcel());

        $this->assertCount(2, $ventures); // el bloque duplicado y el grupo vacío se ignoran
        $this->assertSame('BioCura Ancestral', $ventures[0]['name']);
        $this->assertSame(['Ana Pérez Gómez', 'Luis Gómez'], array_column($ventures[0]['members'], 'name'));
        $this->assertSame('Enfermería', $ventures[0]['members'][0]['program']);
        $this->assertEquals(48100000, $ventures[0]['startup_amount']);
        $this->assertEquals(6000000, $ventures[0]['funding_goal']);
        $this->assertSame(['Sara Ruiz', 'Juan Díaz'], array_column($ventures[1]['members'], 'name')); // varios en una celda
        $this->assertEquals(6000000, $ventures[1]['startup_amount']);
        $this->assertEquals(2000000, $ventures[1]['funding_goal']);
    }

    public function test_admin_imports_excel_with_logos_and_reimport_updates_instead_of_duplicating(): void
    {
        $excel = $this->portfolioExcel();
        $upload = fn () => [
            'edition_id' => $this->edition->id, 'publish' => 1,
            'file' => new UploadedFile($excel, 'portafolio.xlsx', null, null, true),
            'logos' => [UploadedFile::fake()->image('RAIVIX.png'), UploadedFile::fake()->image('Biocura ancestral.jpg')],
        ];

        $this->actingAs($this->admin)->post(route('admin.import.store'), $upload())->assertRedirect(route('admin.import.create'));
        $this->assertSame(2, $this->edition->ventures()->count());
        $bio = $this->edition->ventures()->where('name', 'BioCura Ancestral')->first();
        $this->assertSame('Grupo 1', $bio->team_group);
        $this->assertSame('Parches curativos naturales.', $bio->tagline);
        $this->assertSame('Curar heridas', $bio->mission);
        $this->assertCount(2, $bio->members);
        $this->assertNotNull($bio->logo);
        $this->assertNotNull($this->edition->ventures()->where('name', 'Rayvix')->first()->logo); // RAIVIX.png ≈ Rayvix

        $bio->update(['name' => 'BioCura']); // el admin lo renombra
        $this->actingAs($this->admin)->post(route('admin.import.store'), $upload());
        $this->assertSame(2, $this->edition->ventures()->count());
        $this->assertSame('BioCura', $bio->fresh()->name); // se reconoce por grupo y se conserva el nombre
    }

    public function test_only_admins_can_import(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'jurado']))->get(route('admin.import.create'))->assertForbidden();
    }

    public function test_admin_can_edit_site_settings(): void
    {
        $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'hero_title' => "Ideas que *rugen*\nen Neiva",
            'investment_contact_phone' => '+57 322 385 9086',
            'investment_contact_email' => 'centroemprendimiento@uninavarra.edu.co',
            'contact_site' => 'https://uninavarra.edu.co',
        ])->assertSessionHasNoErrors();

        $this->assertSame('573223859086', Setting::whatsapp('investment_contact_phone'));
        $this->get('/')->assertSee('<span class="text-gradient">rugen</span><br>en Neiva', false);
    }

    public function test_next_call_is_prefilled_from_a_previous_edition(): void
    {
        $this->actingAs($this->admin)->get(route('admin.editions.create', ['desde' => $this->edition->id]))
            ->assertOk()
            ->assertSee('Cátedra UNINAVARRA 2027-1')
            ->assertSee('2027-02-21')        // cronograma corrido 6 meses
            ->assertSee('2027-05-07T10:00')  // gran final corrida 6 meses
            ->assertSee('Innovación');       // rúbrica copiada
    }
}
