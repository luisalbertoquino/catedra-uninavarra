<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Evaluation;
use App\Models\Venture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JuryController extends Controller
{
    /** Emprendimientos de la edición activa con el estado de evaluación del jurado. */
    public function index(Request $request)
    {
        $edition = Edition::where('is_active', true)->with('criteria')->first();
        $ventures = $edition
            ? $edition->ventures()->published()->with('category')->orderBy('name')->get()
            : collect();

        $mine = $edition
            ? $request->user()->evaluations()->where('edition_id', $edition->id)->get()->keyBy('venture_id')
            : collect();

        $filter = $request->get('estado');
        if ($filter === 'pendientes') {
            $ventures = $ventures->reject(fn ($v) => $mine->has($v->id));
        } elseif ($filter === 'evaluados') {
            $ventures = $ventures->filter(fn ($v) => $mine->has($v->id));
        }

        $total = $edition ? $edition->ventures()->published()->count() : 0;

        return view('jury.index', compact('edition', 'ventures', 'mine', 'total', 'filter'));
    }

    public function show(Request $request, Venture $venture)
    {
        $edition = $venture->edition->load('criteria');
        abort_unless($edition->is_active && $venture->is_published, 404);

        $venture->load(['category', 'members']);
        $evaluation = $request->user()->evaluations()->where('venture_id', $venture->id)->with('scores')->first();
        $scores = $evaluation?->scores->pluck('score', 'rubric_criterion_id')->all() ?? [];

        // Navegación entre emprendimientos de la edición
        $ids = $edition->ventures()->published()->orderBy('name')->pluck('id')->values();
        $pos = $ids->search($venture->id);
        $prev = $pos > 0 ? Venture::find($ids[$pos - 1]) : null;
        $next = $pos < $ids->count() - 1 ? Venture::find($ids[$pos + 1]) : null;
        $evaluatedIds = $request->user()->evaluations()->where('edition_id', $edition->id)->pluck('venture_id');
        $nextPending = Venture::whereIn('id', $ids->diff($evaluatedIds)->reject(fn ($id) => $id === $venture->id))->orderBy('name')->first();

        return view('jury.show', compact('venture', 'edition', 'evaluation', 'scores', 'prev', 'next', 'nextPending', 'pos', 'ids'));
    }

    public function store(Request $request, Venture $venture)
    {
        $edition = $venture->edition->load('criteria');
        abort_unless($venture->is_published, 404);
        if (! $edition->isJuryOpen()) {
            return back()->with('error', 'La evaluación de esta edición ya está cerrada.');
        }
        if ($edition->criteria->isEmpty()) {
            return back()->with('error', 'La edición aún no tiene rúbrica configurada. Contacta al administrador.');
        }

        $rules = ['comment' => ['nullable', 'string', 'max:3000']];
        foreach ($edition->criteria as $c) {
            $rules["scores.{$c->id}"] = ['required', 'integer', 'between:1,'.$c->max_score];
        }
        $data = $request->validate($rules, [
            'scores.*.required' => 'Califica todos los criterios de la rúbrica.',
            'scores.*.between' => 'Puntaje fuera de la escala permitida.',
        ]);
        $scores = array_map('intval', $data['scores']);

        DB::transaction(function () use ($request, $venture, $edition, $data, $scores) {
            $evaluation = Evaluation::updateOrCreate(
                ['venture_id' => $venture->id, 'user_id' => $request->user()->id],
                [
                    'edition_id' => $edition->id,
                    'comment' => $data['comment'] ?? null,
                    'total_score' => Evaluation::computeTotal($edition->criteria, $scores),
                ]
            );
            foreach ($edition->criteria as $c) {
                $evaluation->scores()->updateOrCreate(['rubric_criterion_id' => $c->id], ['score' => $scores[$c->id]]);
            }
        });

        $after = $request->input('after') === 'siguiente' && $request->filled('next_id')
            ? Venture::find($request->integer('next_id'))
            : null;

        return redirect($after ? route('jury.show', $after) : route('jury.show', $venture))
            ->with('status', "Evaluación de {$venture->name} guardada.");
    }
}
