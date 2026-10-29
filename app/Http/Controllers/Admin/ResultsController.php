<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ResultsWorkbook;
use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\Evaluation;
use App\Models\User;
use App\Services\JuryScoring;
use App\Services\Ranking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultsController extends Controller
{
    public function index(Request $request)
    {
        $editions = Edition::orderByDesc('year')->orderByDesc('period')->get();
        $edition = $request->filled('edicion') ? $editions->firstWhere('id', (int) $request->edicion) : (Edition::current() ?? $editions->first());
        $ranking = $edition ? Ranking::for($edition, false) : collect();
        [$evaluators, $grades] = $edition ? $this->evaluatorGrades($edition) : [collect(), collect()];

        return view('admin.results.index', compact('editions', 'edition', 'ranking', 'evaluators', 'grades'));
    }

    /**
     * Comité técnico en columnas: primero los jurados y luego el experto, con la nota (1-5) que cada uno dio.
     * Incluye a los jurados actuales y a quien ya haya evaluado en la edición.
     *
     * @return array{0: \Illuminate\Support\Collection<int, User>, 1: \Illuminate\Support\Collection} notas por venture_id y user_id
     */
    private function evaluatorGrades(Edition $edition): array
    {
        $grades = $edition->evaluations()->get(['venture_id', 'user_id', 'total_score'])
            ->groupBy('venture_id')
            ->map(fn ($evals) => $evals->mapWithKeys(fn ($e) => [$e->user_id => JuryScoring::toGrade((float) $e->total_score)]));

        $ids = $edition->evaluations()->distinct()->pluck('user_id');
        $evaluators = User::where('role', 'jurado')->orWhereIn('id', $ids)
            ->orderBy('is_expert')->orderBy('name')->get();

        return [$evaluators, $grades];
    }

    /** Habilita o deshabilita la votación de la comunidad en la página (la alternativa son los votos en redes, registrados a mano). */
    public function toggleWebVoting(Edition $edition)
    {
        $edition->update(['web_voting' => ! $edition->hasWebVoting()]);

        return back()->with('status', $edition->web_voting
            ? 'Votación en la página habilitada: la comunidad vuelve a votar desde cada emprendimiento.'
            : 'Votación en la página deshabilitada: registra en la columna «Votos» los votos de YouTube, Facebook u otras redes.');
    }

    /** Guarda puntajes del comité técnico, finalistas y posiciones finales. */
    public function update(Request $request, Edition $edition)
    {
        $data = $request->validate([
            'rows' => ['array'],
            'rows.*.committee_score' => ['nullable', 'numeric', 'between:0,100'],
            'rows.*.external_votes' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'rows.*.final_position' => ['nullable', 'integer', 'between:1,50'],
        ]);

        DB::transaction(function () use ($edition, $data, $request) {
            foreach ($data['rows'] ?? [] as $id => $row) {
                $fields = [
                    'committee_score' => $row['committee_score'] ?? null,
                    'final_position' => $row['final_position'] ?? null,
                    'is_finalist' => (bool) ($request->input("rows.$id.is_finalist")),
                ];
                // Solo llega cuando la votación es en redes: si no, se conservan los votos ya registrados
                if (array_key_exists('external_votes', $row)) {
                    $fields['external_votes'] = (int) $row['external_votes'];
                }
                $edition->ventures()->whereKey($id)->update($fields);
            }
            $edition->update(['results_published' => $request->boolean('results_published')]);
        });

        return back()->with('status', 'Resultados guardados.');
    }

    /** Marca automáticamente como finalistas a los N primeros según el puntaje ponderado. */
    public function pickFinalists(Edition $edition)
    {
        $ranking = Ranking::for($edition)->filter(fn ($v) => $v->final_grade !== null);
        if ($ranking->isEmpty()) {
            return back()->with('error', 'Aún no hay notas finales: faltan evaluaciones de los jurados.');
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

    /** Borra una evaluación (por ejemplo, de prueba). El jurado puede volver a evaluar mientras la evaluación esté abierta. */
    public function destroyEvaluation(Edition $edition, Evaluation $evaluation)
    {
        abort_unless($evaluation->edition_id === $edition->id, 404);
        $evaluation->delete();

        return back()->with('status', 'Evaluación borrada.');
    }

    /** Borra todas las evaluaciones de un jurado en la edición, sin borrar su cuenta. */
    public function destroyJuryEvaluations(Edition $edition, User $user)
    {
        $count = $edition->evaluations()->where('user_id', $user->id)->delete();

        return back()->with('status', "Se borraron {$count} evaluación(es) de {$user->name}.");
    }

    /** Excel ordenado: resultados, rúbrica por jurado y comentarios (ver ResultsWorkbook). */
    public function export(Edition $edition)
    {
        return (new ResultsWorkbook($edition))->download();
    }
}
