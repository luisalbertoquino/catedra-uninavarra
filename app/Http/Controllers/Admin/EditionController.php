<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\Evaluation;
use App\Models\RubricCriterion;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EditionController extends Controller
{
    public function index()
    {
        $editions = Edition::withCount(['ventures', 'votes'])->orderByDesc('year')->orderByDesc('period')->get();

        return view('admin.editions.index', compact('editions'));
    }

    public function create(Request $request)
    {
        // Precargar con una edición base (la elegida con ?desde= o la más reciente) para no empezar de cero
        $template = $request->filled('desde')
            ? Edition::with(['milestones', 'criteria'])->findOrFail($request->integer('desde'))
            : Edition::with(['milestones', 'criteria'])->orderByDesc('year')->orderByDesc('period')->first();

        // Siguiente convocatoria: 2026-2 → 2027-1, 2027-1 → 2027-2; las fechas se corren 6 meses
        $isNext = $request->filled('desde') && $template;
        [$year, $period] = $isNext
            ? ($template->period == 2 ? [$template->year + 1, 1] : [$template->year, 2])
            : [now()->year, now()->month > 6 ? 2 : 1];
        $shift = fn ($date) => $isNext && $date ? $date->copy()->addMonthsNoOverflow(6) : null;

        $edition = new Edition([
            'name' => $isNext ? trim(preg_replace('/\d{4}(-\d)?/', "{$year}-{$period}", $template->name, 1)) : null,
            'year' => $year,
            'period' => $period,
            'voting_starts_at' => $shift($template?->voting_starts_at),
            'voting_ends_at' => $shift($template?->voting_ends_at),
            'final_at' => $shift($template?->final_at),
            'final_location' => $template->final_location ?? null,
            'event_name' => $template->event_name ?? 'LION HUB EVENT',
            'tagline' => $template->tagline ?? null,
            'description' => $template->description ?? null,
            'community_weight' => $template->community_weight ?? 30,
            'max_votes_per_user' => $template->max_votes_per_user ?? 1,
            'expert_title' => $template->expert_title ?? 'Profesor experto',
            'jury_weight' => $template->jury_weight ?? 40,
            'expert_score_weight' => $template->expert_score_weight ?? 30,
            'vote_grade_mode' => $template->vote_grade_mode ?? 'relativo',
            'vote_grade_thresholds' => $template->vote_grade_thresholds ?? null,
            'finalists_count' => $template->finalists_count ?? 5,
            'voting_email_domain' => $template->voting_email_domain ?? null,
            'promo_video_url' => $template->promo_video_url ?? null,
            'is_published' => true,
        ]);
        $milestones = $template?->milestones->map(fn ($m) => [
            'title' => $m->title, 'description' => $m->description,
            'starts_at' => $shift($m->starts_at)?->format('Y-m-d'), 'ends_at' => $shift($m->ends_at)?->format('Y-m-d'),
        ])->all() ?? [];
        // La rúbrica también se copia de la edición anterior (o se usa la de la Cátedra)
        $criteria = $template && $template->criteria->isNotEmpty()
            ? $template->criteria->map(fn ($c) => $c->only('name', 'description', 'weight', 'max_score'))->all()
            : self::defaultCriteria();

        return view('admin.editions.form', compact('edition', 'milestones', 'criteria'));
    }

    public function store(Request $request)
    {
        $edition = DB::transaction(function () use ($request) {
            $data = $this->validated($request);
            $edition = Edition::create($data);
            $this->syncMilestones($edition, $request->input('milestones', []));
            $this->syncCriteria($edition, $request->input('criteria', []));

            return $edition;
        });

        return redirect()->route('admin.editions.edit', $edition)->with('status', 'Edición creada. Ahora puedes cargar sus emprendimientos.');
    }

    public function edit(Edition $edition)
    {
        $milestones = $edition->milestones->map(fn ($m) => [
            'title' => $m->title, 'description' => $m->description,
            'starts_at' => $m->starts_at?->format('Y-m-d'), 'ends_at' => $m->ends_at?->format('Y-m-d'),
        ])->all();
        $criteria = $edition->criteria->map(fn ($c) => $c->only('id', 'name', 'description', 'weight', 'max_score'))->all();
        $hasEvaluations = $edition->evaluations()->exists();

        return view('admin.editions.form', compact('edition', 'milestones', 'criteria', 'hasEvaluations'));
    }

    public function update(Request $request, Edition $edition)
    {
        DB::transaction(function () use ($request, $edition) {
            $edition->update($this->validated($request, $edition));
            $this->syncMilestones($edition, $request->input('milestones', []));
            $this->syncCriteria($edition, $request->input('criteria', []));
        });

        return back()->with('status', 'Edición actualizada.');
    }

    public function destroy(Edition $edition)
    {
        if ($edition->ventures()->exists()) {
            return back()->with('error', 'No se puede eliminar una edición con emprendimientos. Despublícala en su lugar.');
        }
        Media::delete($edition->banner);
        $edition->delete();

        return redirect()->route('admin.editions.index')->with('status', 'Edición eliminada.');
    }

    public function activate(Edition $edition)
    {
        DB::transaction(function () use ($edition) {
            Edition::whereKeyNot($edition->id)->update(['is_active' => false]);
            $edition->update(['is_active' => true, 'is_published' => true]);
        });

        return back()->with('status', "«{$edition->name}» es ahora la edición activa del sitio.");
    }

    private function validated(Request $request, ?Edition $edition = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'alpha_dash', 'max:150', Rule::unique('editions')->ignore($edition)],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'period' => ['nullable', 'integer', 'in:1,2'],
            'event_name' => ['nullable', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'banner' => ['nullable', 'image', 'max:4096'],
            'promo_video_url' => ['nullable', 'url', 'max:255', 'regex:~(youtube\.com|youtu\.be)/~i'],
            'voting_starts_at' => ['nullable', 'date'],
            'voting_ends_at' => ['nullable', 'date', 'after:voting_starts_at'],
            'final_at' => ['nullable', 'date'],
            'final_location' => ['nullable', 'string', 'max:200'],
            'community_weight' => ['required', 'integer', 'between:0,100'],
            'max_votes_per_user' => ['required', 'integer', 'between:1,50'],
            'finalists_count' => ['required', 'integer', 'between:1,50'],
            'voting_email_domain' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9.-]+\.[a-z]{2,}$/i'],
            'expert_title' => ['required', 'string', 'max:60'],
            'jury_weight' => ['required', 'integer', 'between:0,100'],
            'expert_score_weight' => ['required', 'integer', 'between:0,100'],
            'vote_grade_mode' => ['required', 'in:relativo,rangos'],
            'vote_grade_thresholds' => ['nullable', 'array'],
            'vote_grade_thresholds.*' => ['nullable', 'integer', 'min:0'],
            'criteria' => ['array', 'max:15'],
            'criteria.*.id' => ['nullable', 'integer'],
            'criteria.*.name' => ['required', 'string', 'max:120'],
            'criteria.*.description' => ['nullable', 'string', 'max:500'],
            'criteria.*.weight' => ['required', 'integer', 'between:1,100'],
            'criteria.*.max_score' => ['required', 'integer', 'between:2,10'],
            'milestones' => ['array'],
            'milestones.*.title' => ['required', 'string', 'max:150'],
            'milestones.*.description' => ['nullable', 'string', 'max:1000'],
            'milestones.*.starts_at' => ['nullable', 'date'],
            'milestones.*.ends_at' => ['nullable', 'date'],
        ], ['promo_video_url.regex' => 'El video promocional debe ser un enlace de YouTube.'], ['milestones.*.title' => 'título de la fase', 'promo_video_url' => 'video promocional', 'criteria.*.name' => 'nombre del criterio', 'criteria.*.weight' => 'peso del criterio', 'criteria.*.max_score' => 'puntaje máximo', 'expert_title' => 'nombre del jurado experto', 'jury_weight' => '% jurados', 'expert_score_weight' => '% experto', 'community_weight' => '% comunidad']);

        if ($data['community_weight'] + $data['jury_weight'] + $data['expert_score_weight'] !== 100) {
            throw \Illuminate\Validation\ValidationException::withMessages(['community_weight' => 'Los porcentajes de comunidad, jurados y experto deben sumar 100%.']);
        }
        $data['vote_grade_thresholds'] = collect($data['vote_grade_thresholds'] ?? [])->only(['2', '3', '4', '5'])->map(fn ($v) => (int) $v)->all() ?: null;

        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['is_published'] = $request->boolean('is_published');
        $data['results_published'] = $request->boolean('results_published');
        $data['voting_email_domain'] = isset($data['voting_email_domain']) ? ltrim($data['voting_email_domain'], '@') : null;
        $data['banner'] = Media::store($request->file('banner'), 'editions', $edition?->banner);
        if ($request->boolean('remove_banner')) {
            Media::delete($edition?->banner);
            $data['banner'] = null;
        }
        unset($data['milestones'], $data['criteria']);

        return $data;
    }

    /** Rúbrica por defecto de la Cátedra: 5 criterios de igual peso, escala 1 a 5 */
    private static function defaultCriteria(): array
    {
        return array_map(fn ($c) => ['name' => $c[0], 'description' => $c[1], 'weight' => 20, 'max_score' => 5], RubricCriterion::DEFAULTS);
    }

    /**
     * Actualiza los criterios conservando los existentes (y los puntajes que ya dieron los jurados).
     * Si la rúbrica cambia con evaluaciones hechas, se recalcula el total de cada evaluación.
     */
    private function syncCriteria(Edition $edition, array $rows): void
    {
        $keep = [];
        foreach (array_values($rows) as $i => $row) {
            if (blank($row['name'] ?? null)) {
                continue;
            }
            $criterion = isset($row['id']) ? $edition->criteria()->find($row['id']) : null;
            $criterion ??= $edition->criteria()->make();
            $criterion->fill([
                'name' => $row['name'],
                'description' => $row['description'] ?? null,
                'weight' => (int) $row['weight'],
                'max_score' => (int) $row['max_score'],
                'sort' => $i,
            ])->save();
            $keep[] = $criterion->id;
        }
        $edition->criteria()->whereNotIn('id', $keep)->delete();

        $criteria = $edition->criteria()->get();
        $edition->evaluations()->with('scores')->get()->each(function (Evaluation $e) use ($criteria) {
            $e->update(['total_score' => Evaluation::computeTotal($criteria, $e->scores->pluck('score', 'rubric_criterion_id')->all())]);
        });
    }

    private function syncMilestones(Edition $edition, array $rows): void
    {
        $edition->milestones()->delete();
        foreach (array_values($rows) as $i => $row) {
            if (blank($row['title'] ?? null)) {
                continue;
            }
            $edition->milestones()->create([
                'title' => $row['title'],
                'description' => $row['description'] ?? null,
                'starts_at' => ($row['starts_at'] ?? null) ?: null,
                'ends_at' => ($row['ends_at'] ?? null) ?: null,
                'sort' => $i,
            ]);
        }
    }
}
