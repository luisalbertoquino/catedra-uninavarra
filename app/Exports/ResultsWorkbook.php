<?php

namespace App\Exports;

use App\Models\Edition;
use App\Models\User;
use App\Services\JuryScoring;
use App\Services\Ranking;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Excel de resultados de una edición, en el orden del ranking:
 *  1. Resumen: ganadores y premios, finalistas, porcentajes de la nota, rangos de votos y condiciones.
 *  2. Resultados: nota de cada jurado y del experto, comité técnico, votos, comunidad, aporte de cada parte y nota final.
 *  3. Rúbrica por jurado: el puntaje que cada evaluador dio en cada criterio, agrupado por evaluador.
 *  4. Comentarios: lo que escribió cada evaluador a cada emprendimiento.
 */
class ResultsWorkbook
{
    private const VIOLET = '6D28D9';
    private const BANDS = ['EDE9FE', 'E0F2FE']; // colores alternos para distinguir a cada evaluador
    private const EXPERT_BAND = 'FCE7F3';

    private Collection $ranking;
    private Collection $evaluators;
    private Collection $evaluations;

    public function __construct(private Edition $edition)
    {
        $this->ranking = Ranking::for($edition, false)->values();
        $this->evaluations = $edition->evaluations()->with('scores')->get()->groupBy('venture_id')->map->keyBy('user_id');
        $ids = $edition->evaluations()->distinct()->pluck('user_id');
        // Primero los jurados y al final el experto, como en la tabla de resultados
        $this->evaluators = User::where('role', 'jurado')->orWhereIn('id', $ids)->orderBy('is_expert')->orderBy('name')->get();
    }

    public function download(): StreamedResponse
    {
        $book = new Spreadsheet();
        $book->getProperties()->setTitle('Resultados '.$this->edition->name)->setCreator('LION HUB EVENT');

        $this->overview($book->getActiveSheet());
        $this->summary($book->createSheet());
        $this->rubric($book->createSheet());
        $this->comments($book->createSheet());
        $book->setActiveSheetIndex(0);

        return response()->streamDownload(fn () => (new Xlsx($book))->save('php://output'),
            "resultados-{$this->edition->slug}.xlsx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** Hoja de lectura rápida: quién ganó, qué se llevó y cómo se calculó la nota. */
    private function overview(Worksheet $sheet): void
    {
        $sheet->setTitle('Resumen');
        $e = $this->edition;
        $w = $e->gradeWeights();
        $this->title($sheet, 6, 'Resumen · '.$e->name,
            "Los finalistas se eligen con la nota de 1 a 5; los ganadores los elige el jurado en la gran final.");

        // Ganadores
        $row = $this->section($sheet, 4, 'Ganadores', ['Resultado final', 'Emprendimiento', 'Grupo', 'Nota', 'Votos', 'Premios']);
        $winners = $this->ranking->whereNotNull('final_position')->sortBy('final_position')->values();
        $first = $row;
        foreach ($winners as $v) {
            $prizes = collect($e->prizePlaces()->firstWhere('position', $v->final_position)['items'] ?? [])
                ->map(fn ($i) => $i['title'].(empty($i['amount']) ? '' : ': '.Edition::money($i['amount'])))->implode("\n");
            $sheet->fromArray([$e->placeLabel($v->final_position), $v->name, $v->team_group, $v->final_grade, $v->votes_count, $prizes], null, "A$row", true);
            $sheet->getRowDimension($row)->setRowHeight(max(18, 16 * (substr_count($prizes, "\n") + 1)));
            $row++;
        }
        if ($winners->isEmpty()) {
            $sheet->setCellValue("A$row", 'Aún no se han registrado posiciones finales.');
            $row++;
        }
        $this->sectionBody($sheet, $first - 1, $row - 1, 6);

        // Finalistas, en el orden de la nota
        $row = $this->section($sheet, $row + 1, 'Finalistas', ['Puesto por nota', 'Emprendimiento', 'Grupo', 'Nota', 'Votos', 'Resultado final']);
        $first = $row;
        foreach ($this->ranking as $i => $v) {
            if ($v->is_finalist) {
                $sheet->fromArray([$i + 1, $v->name, $v->team_group, $v->final_grade, $v->votes_count, $v->final_position ? $e->placeLabel($v->final_position) : ''], null, "A$row", true);
                $row++;
            }
        }
        if ($row === $first) {
            $sheet->setCellValue("A$row", 'Aún no se han marcado finalistas.');
            $row++;
        }
        $this->sectionBody($sheet, $first - 1, $row - 1, 6);

        // Porcentajes
        $row = $this->section($sheet, $row + 1, 'Cómo se calcula la nota final', ['Componente', 'Qué mide', 'Peso']);
        $first = $row;
        $parts = $e->expertCountsAsJury()
            ? [['Comité técnico', "Promedio de los jurados y el {$e->expert_title}", $w['jury']]]
            : [['Jurados', 'Promedio de los jurados', $w['jury']], [$e->expert_title, "Nota del {$e->expert_title}", $w['expert']]];
        foreach ([...$parts, ['Comunidad', 'Nota según los votos', $w['community']]] as $part) {
            $sheet->fromArray([$part[0], $part[1], $part[2] / 100], null, "A$row", true);
            $row++;
        }
        $this->sectionBody($sheet, $first - 1, $row - 1, 3);
        $this->numberFormat($sheet, 3, 3, $first, $row - 1, '0%');

        // Votos a nota
        $row = $this->section($sheet, $row + 1, 'De votos a nota de la comunidad', ['Votos', 'Nota']);
        $first = $row;
        if ($e->vote_grade_mode === 'rangos') {
            $ranges = $e->voteRanges();
            foreach ($ranges as $i => $r) {
                $next = $ranges[$i + 1]['min'] ?? null;
                $sheet->setCellValueExplicit("A$row", $next === null ? "{$r['min']} o más" : "{$r['min']} a ".($next - 1), DataType::TYPE_STRING);
                $sheet->setCellValue("B$row", $r['grade']);
                $row++;
            }
        } else {
            $sheet->setCellValue("A$row", 'Proporcional');
            $sheet->setCellValue("B$row", 'El más votado obtiene 5 y los demás en proporción');
            $row++;
        }
        $this->sectionBody($sheet, $first - 1, $row - 1, 2);

        // Premios
        if ($e->hasPrizes()) {
            $row = $this->section($sheet, $row + 1, 'Premios por puesto', ['Puesto', 'Premio', 'Valor (COP)', '', '', 'Condiciones']);
            $sheet->mergeCells($this->range(3, $row - 1, 5, $row - 1));
            $first = $row;
            foreach ($e->prizePlaces() as $place) {
                foreach ($place['items'] as $item) {
                    $details = $item['details'] ?? '';
                    $sheet->fromArray([$place['label'], $item['title'], $item['amount'] ?? null, null, null, $details], null, "A$row", true);
                    $sheet->mergeCells($this->range(3, $row, 5, $row));
                    $sheet->getRowDimension($row)->setRowHeight(max(18, 15 * ceil(mb_strlen($details) / 85)));
                    $row++;
                }
            }
            $this->sectionBody($sheet, $first - 1, $row - 1, 6);
            $this->numberFormat($sheet, 3, 3, $first, $row - 1, '"$" #,##0');
        }

        $this->numberFormat($sheet, 4, 4, 5, $row, '0.00');
        $this->widths($sheet, [1 => 26, 2 => 34, 3 => 12, 4 => 10, 5 => 10, 6 => 70], 6, 12);
    }

    /** Título de un bloque del resumen más su fila de encabezados. Devuelve la primera fila de datos. */
    private function section(Worksheet $sheet, int $row, string $title, array $headers): int
    {
        $sheet->setCellValue("A$row", $title);
        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12)->getColor()->setRGB(self::VIOLET);
        $this->header($sheet, $row + 1, $headers);

        return $row + 2;
    }

    /** Bordes, ajuste de texto y primera columna a la izquierda en un bloque del resumen. */
    private function sectionBody(Worksheet $sheet, int $headerRow, int $last, int $cols): void
    {
        $this->body($sheet, $headerRow, $last, $cols);
        if ($last > $headerRow) {
            $range = $sheet->getStyle($this->range(1, $headerRow + 1, $cols, $last));
            $range->getAlignment()->setWrapText(true);
            $sheet->getStyle($this->range(1, $headerRow + 1, 1, $last))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            if ($cols === 6) {
                $sheet->getStyle($this->range(6, $headerRow + 1, 6, $last))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            }
        }
    }

    /** Cuánto suma cada parte a la nota final: nota × peso (si falta una parte, su peso se reparte como en Ranking). */
    private function shares($v): array
    {
        $w = $this->edition->gradeWeights();
        $grades = ['community' => $v->community_grade, 'jury' => $v->jury_grade, 'expert' => $this->edition->expertCountsAsJury() ? null : $v->expert_grade];
        $present = collect($grades)->filter(fn ($g, $k) => $g !== null && $w[$k] > 0);
        $available = $present->keys()->sum(fn ($k) => $w[$k]);
        if ($v->final_grade === null || $available === 0) {
            return ['community' => null, 'jury' => null, 'expert' => null];
        }

        return collect($grades)->map(fn ($g, $k) => $present->has($k) ? round($g * $w[$k] / $available, 2) : null)->all();
    }

    private function summary(Worksheet $sheet): void
    {
        $sheet->setTitle('Resultados');
        $e = $this->edition;
        $w = $e->gradeWeights();
        $combined = $e->expertCountsAsJury();

        $headers = ['#', 'Emprendimiento', 'Grupo', 'Categoría',
            ...$this->evaluators->map(fn ($u) => $this->role($u)."\n".$u->name),
            ...($combined
                ? ["Comité técnico\n(peso {$w['jury']}%)", "Aporte comité\n(nota × {$w['jury']}%)"]
                : ["Jurados\n(peso {$w['jury']}%)", "Aporte jurados\n(nota × {$w['jury']}%)", "{$e->expert_title}\n(peso {$w['expert']}%)", "Aporte experto\n(nota × {$w['expert']}%)"]),
            $e->hasWebVoting() ? "Votos\nen la página" : "Votos\nen redes",
            "Comunidad\n(peso {$w['community']}%)", "Aporte comunidad\n(nota × {$w['community']}%)",
            'Nota final', 'Estado', 'Finalista', 'Posición final', 'Resultado final'];

        $rows = $this->ranking->map(function ($v, $i) use ($combined, $e) {
            $share = $this->shares($v);

            return [
                $i + 1, $v->name, $v->team_group, $v->category?->name,
                ...$this->evaluators->map(fn ($u) => $this->grade($v->id, $u->id)),
                ...($combined ? [$v->jury_grade, $share['jury']] : [$v->jury_grade, $share['jury'], $v->expert_grade, $share['expert']]),
                $v->votes_count, $v->community_grade, $share['community'],
                $v->final_grade, $v->is_partial ? 'Parcial' : '', $v->is_finalist ? 'Sí' : 'No', $v->final_position,
                $v->final_position ? $e->placeLabel($v->final_position) : '',
            ];
        });

        $this->title($sheet, count($headers), 'Resultados · '.$e->name,
            $combined
                ? "Nota final (1 a 5) = comité técnico {$w['jury']}% (promedio de jurados y {$e->expert_title}) + comunidad {$w['community']}%."
                : "Nota final (1 a 5) = jurados {$w['jury']}% + {$e->expert_title} {$w['expert']}% + comunidad {$w['community']}%.");
        $this->header($sheet, 4, $headers);
        $sheet->fromArray($rows->all(), null, 'A5', true);

        $last = 4 + $rows->count();
        $firstGrade = 5;
        $finalCol = count($headers) - 4;
        $this->body($sheet, 4, $last, count($headers));
        $this->numberFormat($sheet, $firstGrade, $finalCol, 5, $last, '0.00');
        $votesCol = $finalCol - 3;
        $this->numberFormat($sheet, $votesCol, $votesCol, 5, $last, '#,##0');
        $this->fill($sheet, $finalCol, $finalCol, 5, $last, 'F5F3FF');
        $sheet->getStyle($this->range($finalCol, 5, $finalCol, $last))->getFont()->setBold(true);

        $this->widths($sheet, [1 => 5, 2 => 30, 3 => 11, 4 => 22, count($headers) => 24], count($headers), 14);
        $sheet->freezePane('C5');
    }

    /** Puntaje por criterio: una banda de columnas por evaluador (criterios + su nota), con el nombre arriba. */
    private function rubric(Worksheet $sheet): void
    {
        $sheet->setTitle('Rúbrica por jurado');
        $criteria = $this->edition->criteria;
        $per = $criteria->count() + 1;
        $total = 3 + $this->evaluators->count() * $per + 1;

        $this->title($sheet, $total, 'Rúbrica por jurado · '.$this->edition->name,
            'Puntaje que cada evaluador dio en cada criterio (escala de 0 al máximo indicado) y su nota de 1 a 5. Celdas vacías = aún no evalúa.');

        // Fila 4: evaluadores (celdas combinadas) · Fila 5: criterios
        $this->header($sheet, 4, ['#', 'Emprendimiento', 'Grupo']);
        $this->header($sheet, 5, ['', '', '']);
        foreach (['A', 'B', 'C'] as $col) {
            $sheet->mergeCells("{$col}4:{$col}5");
        }
        foreach ($this->evaluators->values() as $i => $u) {
            $from = 4 + $i * $per;
            $to = $from + $per - 1;
            $band = $u->is_expert ? self::EXPERT_BAND : self::BANDS[$i % 2];
            $sheet->setCellValue($this->cell($from, 4), $this->role($u).': '.$u->name);
            $sheet->mergeCells($this->range($from, 4, $to, 4));
            $sheet->getStyle($this->cell($from, 4))->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle($this->cell($from, 4))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $this->header($sheet, 5, [...$criteria->map(fn ($c) => "{$c->name}\n(máx. {$c->max_score})"), 'Nota'], $from);
            $this->fill($sheet, $from, $to, 4, 5, $band);
            $sheet->getStyle($this->range($from, 4, $to, 5))->getFont()->getColor()->setRGB('1E1B4B');
        }
        $this->header($sheet, 4, [$this->edition->committeeLabel()], $total);
        $this->header($sheet, 5, [''], $total);
        $sheet->mergeCells($this->range($total, 4, $total, 5));

        $rows = $this->ranking->map(function ($v, $i) use ($criteria) {
            $cells = [$i + 1, $v->name, $v->team_group];
            foreach ($this->evaluators as $u) {
                $eval = $this->evaluations->get($v->id)?->get($u->id);
                foreach ($criteria as $c) {
                    $s = $eval?->scores->firstWhere('rubric_criterion_id', $c->id)?->score;
                    $cells[] = $s === null ? null : (float) $s;
                }
                $cells[] = $this->grade($v->id, $u->id);
            }
            $cells[] = $v->jury_grade;

            return $cells;
        });
        $sheet->fromArray($rows->all(), null, 'A6', true);

        $last = 5 + $rows->count();
        $this->body($sheet, 5, $last, $total);
        $this->numberFormat($sheet, 4, $total, 6, $last, '0.0#');
        foreach ($this->evaluators->values() as $i => $u) {
            $notaCol = 4 + $i * $per + $per - 1;
            $this->numberFormat($sheet, $notaCol, $notaCol, 6, $last, '0.00');
            $this->fill($sheet, $notaCol, $notaCol, 6, $last, $u->is_expert ? self::EXPERT_BAND : self::BANDS[$i % 2]);
            $sheet->getStyle($this->range($notaCol, 6, $notaCol, $last))->getFont()->setBold(true);
            // Línea gruesa entre evaluadores
            $sheet->getStyle($this->range($notaCol, 4, $notaCol, $last))->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB(self::VIOLET);
        }
        $this->numberFormat($sheet, $total, $total, 6, $last, '0.00');
        $sheet->getStyle($this->range($total, 6, $total, $last))->getFont()->setBold(true);

        $this->widths($sheet, [1 => 5, 2 => 30, 3 => 11], $total, 13);
        $sheet->getRowDimension(5)->setRowHeight(48);
        $sheet->freezePane('D6');
    }

    private function comments(Worksheet $sheet): void
    {
        $sheet->setTitle('Comentarios');
        $this->title($sheet, 5, 'Comentarios de los evaluadores · '.$this->edition->name, 'En el orden del ranking y de los evaluadores.');
        $this->header($sheet, 4, ['Emprendimiento', 'Grupo', 'Evaluador', 'Nota', 'Comentario']);

        $rows = $this->ranking->flatMap(fn ($v) => $this->evaluators
            ->map(fn ($u) => [$v, $u, $this->evaluations->get($v->id)?->get($u->id)])
            ->filter(fn ($r) => filled($r[2]?->comment))
            ->map(fn ($r) => [$r[0]->name, $r[0]->team_group, $this->role($r[1]).': '.$r[1]->name, $this->grade($r[0]->id, $r[1]->id), $r[2]->comment]));
        $sheet->fromArray($rows->values()->all(), null, 'A5', true);

        $last = 4 + $rows->count();
        $this->body($sheet, 4, $last, 5);
        $this->numberFormat($sheet, 4, 4, 5, $last, '0.00');
        $sheet->getStyle($this->range(5, 5, 5, max(5, $last)))->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $this->widths($sheet, [1 => 30, 2 => 11, 3 => 30, 4 => 8, 5 => 80], 5, 12);
        $sheet->freezePane('A5');
    }

    // ---------------------------------------------------------------- utilidades

    private function grade(int $ventureId, int $userId): ?float
    {
        $e = $this->evaluations->get($ventureId)?->get($userId);

        return $e ? JuryScoring::toGrade((float) $e->total_score) : null;
    }

    private function role(User $u): string
    {
        return $u->is_expert ? $this->edition->expert_title : 'Jurado';
    }

    private function title(Worksheet $sheet, int $cols, string $title, string $subtitle): void
    {
        $sheet->setCellValue('A1', $title)->setCellValue('A2', $subtitle);
        $sheet->mergeCells($this->range(1, 1, $cols, 1));
        $sheet->mergeCells($this->range(1, 2, $cols, 2));
        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setRGB(self::VIOLET);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('475569');
    }

    private function header(Worksheet $sheet, int $row, array $values, int $fromCol = 1): void
    {
        foreach (array_values($values) as $i => $value) {
            $sheet->setCellValue($this->cell($fromCol + $i, $row), $value);
        }
        $style = $sheet->getStyle($this->range($fromCol, $row, $fromCol + count($values) - 1, $row));
        $style->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::VIOLET);
        $style->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(36);
    }

    /** Bordes finos, centrado y nombres alineados a la izquierda */
    private function body(Worksheet $sheet, int $headerRow, int $last, int $cols): void
    {
        $all = $sheet->getStyle($this->range(1, $headerRow, $cols, max($headerRow, $last)));
        $all->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
        if ($last > $headerRow) {
            $sheet->getStyle($this->range(1, $headerRow + 1, $cols, $last))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($this->range(2, $headerRow + 1, 2, $last))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle($this->range(2, $headerRow + 1, 2, $last))->getFont()->setBold(true);
        }
    }

    private function numberFormat(Worksheet $sheet, int $fromCol, int $toCol, int $fromRow, int $toRow, string $format): void
    {
        if ($toRow >= $fromRow) {
            $sheet->getStyle($this->range($fromCol, $fromRow, $toCol, $toRow))->getNumberFormat()->setFormatCode($format);
        }
    }

    private function fill(Worksheet $sheet, int $fromCol, int $toCol, int $fromRow, int $toRow, string $rgb): void
    {
        if ($toRow >= $fromRow) {
            $sheet->getStyle($this->range($fromCol, $fromRow, $toCol, $toRow))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rgb);
        }
    }

    private function widths(Worksheet $sheet, array $fixed, int $cols, int $default): void
    {
        for ($c = 1; $c <= $cols; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth($fixed[$c] ?? $default);
        }
    }

    private function cell(int $col, int $row): string
    {
        return Coordinate::stringFromColumnIndex($col).$row;
    }

    private function range(int $c1, int $r1, int $c2, int $r2): string
    {
        return $this->cell($c1, $r1).':'.$this->cell($c2, $r2);
    }
}
