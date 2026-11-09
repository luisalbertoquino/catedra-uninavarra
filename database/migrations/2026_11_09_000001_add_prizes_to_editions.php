<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Premios por puesto de cada edición. Carga los de la edición 2026-2, entregados por la Cátedra. */
return new class extends Migration
{
    private const AVAL = 'Los estudiantes ganadores del LION HUB EVENT - UNINAVARRA podrán desarrollar, a partir de su idea innovadora, un Plan de Negocios o Plan Empresarial que les permita cumplir bajo la modalidad de EMPRENDIMIENTO con su requisito de grado para obtener su título profesional, teniendo en cuenta lo establecido en la normatividad institucional.';

    private const BONO_MATRICULA = 'El valor del bono de descuento será únicamente aplicado en el periodo 2027-1 al costo de los Derechos Pecuniarios por concepto de matrícula de los ganadores; no se realizarán entregas en efectivo. Si el ganador es de último semestre, el bono será aplicado a los derechos de grado y el valor del bono será por este monto.';

    private const BONO_INVERSION = 'El presente bono de inversión será otorgado exclusivamente para el fortalecimiento, desarrollo y ejecución de la idea de negocio ganadora presentada en el marco del Lion Hub Event. Los recursos asignados deberán destinarse únicamente a la adquisición de insumos, materiales, herramientas, servicios o elementos necesarios para la puesta en marcha y consolidación de la propuesta emprendedora. Asimismo, la ejecución del bono deberá realizarse de manera articulada y con acompañamiento del Centro de Emprendimiento y Sostenibilidad de UNINAVARRA, garantizando el adecuado uso de los recursos y el cumplimiento de los objetivos del proyecto. Aplica términos y condiciones.';

    public function up(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->json('prizes')->nullable()->after('finalists_count');
        });

        $aval = ['title' => 'Aval para modalidad de grado', 'amount' => null, 'details' => self::AVAL];
        $place = fn (int $matricula, int $inversion) => [
            $aval,
            ['title' => 'Bono de descuento en la matrícula', 'amount' => $matricula, 'details' => self::BONO_MATRICULA],
            ['title' => 'Bono de inversión', 'amount' => $inversion, 'details' => self::BONO_INVERSION],
        ];

        DB::table('editions')->where('year', 2026)->where('period', 2)->update(['prizes' => json_encode([
            ['position' => 1, 'label' => 'Primer puesto', 'items' => $place(1000000, 5000000)],
            ['position' => 2, 'label' => 'Segundo puesto', 'items' => $place(700000, 2500000)],
            ['position' => 3, 'label' => 'Reconocimiento especial', 'items' => [$aval]],
        ], JSON_UNESCAPED_UNICODE)]);
    }

    public function down(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn('prizes');
        });
    }
};
