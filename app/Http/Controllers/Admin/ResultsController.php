<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\User;
use App\Services\JuryScoring;
use App\Services\Ranking;
use App\Support\Csv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultsController extends Controller
{
    public function index(Request $request)
    {
        $editions = Edition::orderByDesc('year')->orderByDesc('period')->get();
        $edition = $request->filled('edicion') ? $editions->firstWhere('id', (int) $request->edicion) : (Edition::current() ?? $editions->first());
        $ranking = $edition ? Ranking::for($edition, false) : collect();

        return view('admin.results.index', compact('editions', 'edition', 'ranking'));
    }

    /** Guarda puntajes del comité técnico, finalistas y posiciones finales. */
    public function update(Request $request, Edition $edition)
    {
        $data = $request->validate([
            'rows' => ['array'],
            'rows.*.committee_score' => ['nullable', 'numeric', 'between:0,100'],
            'rows.*.final_position' => ['nullable', 'integer', 'between:1,50'],
        ]);

        DB::transaction(function () use ($edition, $data, $request) {
            foreach ($data['rows'] ?? [] as $id => $row) {
                $edition->ventures()->whereKey($id)->update([
                    'committee_score' => $row['committee_score'] ?? null,
                    'final_position' => $row['final_position'] ?? null,
                    'is_finalist' => (bool) ($request->input("rows.$id.is_finalist")),
                ]);
            }
            $edition->update(['results_published' => $request->boolean('results_published')]);
        });

        return back()->with('status', 'Resultados guardados.');
    }

    /** Marca automáticamente como finalistas a los N primeros según el puntaje ponderado. */
    public function pickFinalists(Edition $edition)
    {
        $ranking = Ranking::for($edition)->filter(fn ($v) => $v->final_score !== null);
        if ($ranking->isEmpty()) {
            return back()->with('error', 'Primero registra los puntajes del comité técnico.');
        }
        $ids = $ranking->take($edition->finalists_count)->pluck('id');

        DB::transaction(function () use ($edition, $ids) {
            $edition->ventures()->update(['is_finalist' => false]);
            $edition->ventures()->whereIn('id', $ids)->update(['is_finalist' => true]);
        });

        return back()->with('status', "Se marcaron {$ids->count()} finalistas según el puntaje ponderado.");
    }

    /** Matriz de evaluaciones: emprendimientos × jurados, con el detalle por criterio. */
    public function evaluations(Edition $edition)
    {
        $edition->load('criteria');
        $juries = User::where('role', 'jurado')->orderByDesc('is_expert')->orderBy('name')->get();
        $ventures = $edition->ventures()->orderBy('name')->get();
        $evaluations = $edition->evaluations()->with('scores')->get()->groupBy('venture_id')->map->keyBy('user_id');
        $scores = JuryScoring::forEdition($edition);

        return view('admin.results.evaluations', compact('edition', 'juries', 'ventures', 'evaluations', 'scores'));
    }

    public function export(Edition $edition)
    {
        $rows = Ranking::for($edition, false)->values()->map(fn ($v, $i) => [
            $i + 1, $v->name, $v->category?->name, $v->votes_count, $v->community_score,
            $v->committee_total, $v->committee_source === 'jurados' ? "Jurados ({$v->jury_count})" : ($v->committee_source === 'manual' ? 'Manual' : ''),
            $v->final_score, $v->is_finalist ? 'Sí' : 'No', $v->final_position,
            $v->comments_count, $v->rating_avg ? round($v->rating_avg, 1) : null, $v->views,
        ]);

        return Csv::download("resultados-{$edition->slug}.csv",
            ['#', 'Emprendimiento', 'Categoría', 'Votos', 'Puntaje comunidad', 'Puntaje comité', 'Fuente comité', 'Puntaje final', 'Finalista', 'Posición', 'Comentarios', 'Calificación', 'Visitas'],
            $rows);
    }
}
