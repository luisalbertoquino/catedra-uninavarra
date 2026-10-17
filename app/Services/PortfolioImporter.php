<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\Venture;
use App\Support\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

/**
 * Importa el "portafolio" de emprendimientos de una edición desde el Excel que recoge la Cátedra.
 *
 * Formato esperado (una hoja, primera fila con encabezados; se reconocen sin importar tildes o mayúsculas):
 *   Nombre | Programa | Grupo | Nombre de la Idea de Negocio | Descripción de la idea | Población objetivo |
 *   Misión | Visión | Monto estimado para iniciar | Monto que solicitaría a un inversionista | Logo
 *
 * Cada emprendimiento empieza en la fila que trae el nombre de la idea; las filas siguientes con solo
 * "Nombre" y "Programa" son los integrantes. Los bloques repetidos se ignoran y si se vuelve a importar,
 * los emprendimientos se actualizan (se reconocen por grupo o por nombre).
 */
class PortfolioImporter
{
    private const COLUMNS = [
        'name' => ['nombre'],
        'program' => ['programa'],
        'group' => ['grupo'],
        'idea' => ['nombre de la idea', 'idea de negocio', 'emprendimiento'],
        'description' => ['descripcion'],
        'target' => ['poblacion objetivo', 'poblacion', 'mercado'],
        'mission' => ['mision'],
        'vision' => ['vision'],
        'startup' => ['monto estimado', 'monto para iniciar'],
        'investor' => ['monto que solicitaria', 'solicitaria a un inversionista', 'monto solicitado'],
        'logo' => ['logo'],
    ];

    /** Programas académicos escritos de varias formas en el Excel → forma oficial */
    private const PROGRAMS = [
        'enfermeria' => 'Enfermería', 'enfermeria fds' => 'Enfermería FDS', 'derecho' => 'Derecho', 'medicina' => 'Medicina',
        'radiologia' => 'Tecnología en Radiología e Imágenes Diagnósticas',
        'tecnologo en radiologia e imagenes diagnosticas' => 'Tecnología en Radiología e Imágenes Diagnósticas',
    ];

    /** Lee el Excel y devuelve los emprendimientos encontrados (sin guardar nada). */
    public function parse(string $path): array
    {
        $sheet = IOFactory::load($path)->getSheet(0);
        $rows = $sheet->toArray(null, true, false, true); // [fila => [columna => valor]]
        $headerRow = array_shift($rows);
        $map = $this->mapColumns($headerRow);
        if (! isset($map['idea'])) {
            throw new \RuntimeException('No se encontró la columna "Nombre de la Idea de Negocio" en la primera fila del Excel.');
        }

        $ventures = [];
        $seen = [];
        $current = null;
        foreach ($rows as $rowNumber => $row) {
            $get = fn ($field) => isset($map[$field]) ? $this->clean($row[$map[$field]] ?? null) : null;
            $idea = $get('idea');
            $name = $get('name');
            $group = $get('group');

            if ($idea) {
                $key = $this->normalize($idea);
                if (isset($seen[$key])) { // bloque duplicado
                    $current = null;
                    continue;
                }
                $seen[$key] = true;
                $ventures[] = [
                    'name' => $idea, 'group' => $group ? Str::ucfirst(Str::lower($group)) : null,
                    'description' => $get('description'), 'target' => $get('target'),
                    'mission' => $get('mission'), 'vision' => $get('vision'),
                    'startup_amount' => $this->amount($get('startup')), 'funding_goal' => $this->amount($get('investor')),
                    'members' => [], 'rows' => [$rowNumber, $rowNumber], 'embedded_logo' => null,
                ];
                $current = array_key_last($ventures);
            } elseif ($group) { // grupo sin información: no se importa
                $current = null;
                continue;
            }

            if ($current === null) {
                continue;
            }
            $ventures[$current]['rows'][1] = $rowNumber;
            if (! $ventures[$current]['startup_amount'] && ($s = $this->amount($get('startup')))) {
                $ventures[$current]['startup_amount'] = $s;
                $ventures[$current]['funding_goal'] ??= $this->amount($get('investor'));
            }
            // Integrantes: uno por fila, o varios separados por comas en una sola celda
            foreach (preg_split('/\s*[,;\n]\s*/', (string) $name) as $person) {
                if ($person !== '' && mb_strlen($person) > 2) {
                    $this->addMember($ventures[$current]['members'], $person, $get('program'));
                }
            }
        }

        // Logos pegados dentro del Excel: se asignan al emprendimiento cuyo bloque contiene la imagen
        foreach ($sheet->getDrawingCollection() as $drawing) {
            $row = (int) preg_replace('/\D/', '', $drawing->getCoordinates());
            foreach ($ventures as &$v) {
                if (! $v['embedded_logo'] && $row >= $v['rows'][0] && $row <= $v['rows'][1]) {
                    $v['embedded_logo'] = $this->drawingContents($drawing);
                }
            }
            unset($v);
        }

        return $ventures;
    }

    /**
     * Guarda los emprendimientos en la edición.
     *
     * @param  UploadedFile[]|string[]  $logos  archivos de logo (se asignan por nombre aproximado)
     */
    public function import(Edition $edition, array $ventures, array $logos = [], bool $publish = true): array
    {
        $report = ['created' => [], 'updated' => [], 'logos' => 0, 'warnings' => []];

        DB::transaction(function () use ($edition, $ventures, $logos, $publish, &$report) {
            foreach ($ventures as $data) {
                $venture = $this->findExisting($edition, $data) ?? new Venture(['edition_id' => $edition->id, 'is_published' => $publish]);
                $isNew = ! $venture->exists;

                $programs = collect($data['members'])->pluck('program')->filter()->countBy()->sortDesc();
                $venture->fill(array_filter([
                    'name' => $isNew ? $data['name'] : $venture->name, // si el admin ya lo renombró, se conserva
                    'team_group' => $data['group'],
                    'tagline' => $isNew ? $this->tagline($data['description']) : null,
                    'description' => $data['description'], 'target_market' => $data['target'],
                    'mission' => $data['mission'], 'vision' => $data['vision'],
                    'startup_amount' => $data['startup_amount'], 'funding_goal' => $data['funding_goal'],
                    'academic_program' => $programs->keys()->first(),
                ], fn ($v) => $v !== null));
                if ($isNew) {
                    $venture->slug = $this->uniqueSlug($data['name']);
                }

                // Logo: archivo subido que coincida por nombre > imagen del Excel > se conserva el actual
                if ($file = $this->matchLogo($data['name'], $logos)) {
                    $venture->logo = $this->storeLogo($file, $venture->logo);
                    $report['logos']++;
                } elseif ($data['embedded_logo'] && ! $venture->logo) {
                    [$bytes, $ext] = $data['embedded_logo'];
                    $path = 'ventures/logos/'.Str::random(32).'.'.$ext;
                    Storage::disk('public')->put($path, $bytes);
                    $venture->logo = $path;
                    $report['logos']++;
                }
                $venture->save();

                if ($data['members']) {
                    $venture->members()->delete();
                    foreach (array_values($data['members']) as $i => $m) {
                        $venture->members()->create(['name' => $m['name'], 'academic_program' => $m['program'], 'sort' => $i]);
                    }
                }

                $report[$isNew ? 'created' : 'updated'][] = $venture->name;
                foreach (['description' => 'descripción', 'mission' => 'misión', 'vision' => 'visión'] as $field => $label) {
                    if (blank($data[$field])) {
                        $report['warnings'][] = "{$venture->name}: falta la {$label}.";
                    }
                }
                if (! $venture->logo) {
                    $report['warnings'][] = "{$venture->name}: sin logo.";
                }
            }
        });

        return $report;
    }

    // ------------------------------------------------------------------ utilidades

    /** Frase corta: primera oración de la descripción (o recorte por palabras). */
    public function tagline(?string $description): ?string
    {
        if (! $description) {
            return null;
        }
        $text = trim(preg_replace('/\s+/', ' ', trim($description, " \"'“”«»")));
        $first = preg_split('/(?<=[.!?])\s/u', $text)[0];

        return Str::ucfirst(rtrim(mb_strlen($first) <= 160 ? $first : Str::words($first, 22, '…'), ' ,;:'));
    }

    private function mapColumns(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $col => $title) {
            $t = $this->normalize((string) $title, keepSpaces: true);
            foreach (self::COLUMNS as $field => $needles) {
                if (! isset($map[$field]) && collect($needles)->contains(fn ($n) => $t === $n || str_starts_with($t, $n))) {
                    $map[$field] = $col;
                    break;
                }
            }
        }

        return $map;
    }

    private function addMember(array &$members, string $person, ?string $program): void
    {
        $name = Str::title(Str::lower(preg_replace('/\s+/', ' ', trim($person))));
        if (collect($members)->contains(fn ($m) => $this->normalize($m['name']) === $this->normalize($name))) {
            return;
        }
        $members[] = ['name' => $name, 'program' => $this->program($program)];
    }

    private function program(?string $program): ?string
    {
        if (! $program) {
            return null;
        }
        $key = $this->normalize($program, keepSpaces: true);

        return self::PROGRAMS[$key] ?? Str::ucfirst(trim($program));
    }

    /** "$48.100.000,00 COP" → 48100000 · "$6,000,000" → 6000000 · "30.800.000 COP" → 30800000 */
    public function amount(?string $value): ?float
    {
        if (! $value || ! preg_match('/\d[\d.,]*/', $value, $m)) {
            return null;
        }
        $n = rtrim($m[0], '.,');
        if (preg_match('/[.,]\d{2}$/', $n) && preg_match_all('/[.,]/', $n) > 1) {
            $n = substr($n, 0, -3); // quita los centavos (",00")
        }
        $digits = preg_replace('/\D/', '', $n);

        return $digits === '' ? null : (float) $digits;
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim(preg_replace('/[ \t]+/', ' ', str_replace(["\r\n", "\r"], "\n", (string) $value)));

        return $value === '' ? null : $value;
    }

    private function normalize(string $text, bool $keepSpaces = false): string
    {
        $t = Str::lower(Str::ascii($text));

        return $keepSpaces ? trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9 ]/', ' ', $t))) : preg_replace('/[^a-z0-9]/', '', $t);
    }

    private function findExisting(Edition $edition, array $data): ?Venture
    {
        $ventures = $edition->ventures()->get();

        return $ventures->first(fn ($v) => $data['group'] && $v->team_group && $this->normalize($v->team_group) === $this->normalize($data['group']))
            ?? $ventures->first(fn ($v) => $this->normalize($v->name) === $this->normalize($data['name']));
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'emprendimiento';
        $slug = $base;
        for ($i = 2; Venture::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    /** Busca entre los logos el que corresponde al emprendimiento: igual, prefijo o diferencia de 1-2 letras. */
    private function matchLogo(string $name, array $logos): UploadedFile|string|null
    {
        $target = $this->normalize($name);
        $best = null;
        $bestScore = PHP_INT_MAX;
        foreach ($logos as $file) {
            $filename = $file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file);
            $candidate = $this->normalize(pathinfo($filename, PATHINFO_FILENAME));
            if ($candidate === '') {
                continue;
            }
            $score = match (true) {
                $candidate === $target => 0,
                strlen($candidate) >= 4 && (str_starts_with($target, $candidate) || str_starts_with($candidate, $target)) => 1,
                strlen($target) >= 5 && levenshtein($candidate, $target) <= 2 => 1 + levenshtein($candidate, $target),
                default => PHP_INT_MAX,
            };
            if ($score < $bestScore) {
                [$best, $bestScore] = [$file, $score];
            }
        }

        return $best;
    }

    private function storeLogo(UploadedFile|string $file, ?string $previous): string
    {
        if ($file instanceof UploadedFile) {
            return Media::store($file, 'ventures/logos', $previous);
        }
        Media::delete($previous);
        $path = 'ventures/logos/'.Str::random(32).'.'.strtolower(pathinfo($file, PATHINFO_EXTENSION));
        Storage::disk('public')->put($path, file_get_contents($file));

        return $path;
    }

    /** @return array{0: string, 1: string}|null [bytes, extensión] */
    private function drawingContents(object $drawing): ?array
    {
        if ($drawing instanceof Drawing) {
            $bytes = @file_get_contents($drawing->getPath());

            return $bytes ? [$bytes, strtolower($drawing->getExtension() ?: 'png')] : null;
        }
        if ($drawing instanceof MemoryDrawing) {
            ob_start();
            imagepng($drawing->getImageResource());

            return [ob_get_clean(), 'png'];
        }

        return null;
    }
}
