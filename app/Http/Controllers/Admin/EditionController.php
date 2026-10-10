<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
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

    public function create()
    {
        // Precargar con la edición anterior para no empezar de cero cada semestre
        $template = Edition::with('milestones')->orderByDesc('year')->orderByDesc('period')->first();
        $edition = new Edition([
            'year' => now()->year,
            'period' => now()->month > 6 ? 2 : 1,
            'event_name' => $template->event_name ?? 'LION HUB EVENT',
            'tagline' => $template->tagline ?? null,
            'description' => $template->description ?? null,
            'community_weight' => $template->community_weight ?? 30,
            'max_votes_per_user' => $template->max_votes_per_user ?? 3,
            'finalists_count' => $template->finalists_count ?? 5,
            'voting_email_domain' => $template->voting_email_domain ?? null,
            'is_published' => true,
        ]);
        $milestones = $template?->milestones->map(fn ($m) => ['title' => $m->title, 'description' => $m->description, 'starts_at' => null, 'ends_at' => null])->all() ?? [];

        return view('admin.editions.form', compact('edition', 'milestones'));
    }

    public function store(Request $request)
    {
        $edition = DB::transaction(function () use ($request) {
            $data = $this->validated($request);
            $edition = Edition::create($data);
            $this->syncMilestones($edition, $request->input('milestones', []));

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

        return view('admin.editions.form', compact('edition', 'milestones'));
    }

    public function update(Request $request, Edition $edition)
    {
        DB::transaction(function () use ($request, $edition) {
            $edition->update($this->validated($request, $edition));
            $this->syncMilestones($edition, $request->input('milestones', []));
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
            'voting_starts_at' => ['nullable', 'date'],
            'voting_ends_at' => ['nullable', 'date', 'after:voting_starts_at'],
            'final_at' => ['nullable', 'date'],
            'final_location' => ['nullable', 'string', 'max:200'],
            'community_weight' => ['required', 'integer', 'between:0,100'],
            'max_votes_per_user' => ['required', 'integer', 'between:1,50'],
            'finalists_count' => ['required', 'integer', 'between:1,50'],
            'voting_email_domain' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9.-]+\.[a-z]{2,}$/i'],
            'milestones' => ['array'],
            'milestones.*.title' => ['required', 'string', 'max:150'],
            'milestones.*.description' => ['nullable', 'string', 'max:1000'],
            'milestones.*.starts_at' => ['nullable', 'date'],
            'milestones.*.ends_at' => ['nullable', 'date'],
        ], [], ['milestones.*.title' => 'título de la fase']);

        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['is_published'] = $request->boolean('is_published');
        $data['results_published'] = $request->boolean('results_published');
        $data['voting_email_domain'] = isset($data['voting_email_domain']) ? ltrim($data['voting_email_domain'], '@') : null;
        $data['banner'] = Media::store($request->file('banner'), 'editions', $edition?->banner);
        if ($request->boolean('remove_banner')) {
            Media::delete($edition?->banner);
            $data['banner'] = null;
        }
        unset($data['milestones']);

        return $data;
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
