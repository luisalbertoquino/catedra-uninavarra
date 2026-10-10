<?php

namespace App\Http\Controllers;

use App\Models\Edition;

class EditionController extends Controller
{
    public function index()
    {
        $editions = Edition::published()
            ->withCount(['ventures' => fn ($q) => $q->published(), 'votes'])
            ->with(['ventures' => fn ($q) => $q->published()->whereNotNull('final_position')->orderBy('final_position')])
            ->orderByDesc('year')->orderByDesc('period')->get();

        return view('public.editions.index', compact('editions'));
    }

    public function show(Edition $edition)
    {
        abort_unless($edition->is_published, 404);

        $edition->load('milestones')->loadCount('votes');
        $ventures = $edition->ventures()->published()->with('category')->withCount('votes')
            ->orderByRaw('final_position is null')->orderBy('final_position')
            ->orderByDesc('is_finalist')->orderByDesc('votes_count')->get();

        return view('public.editions.show', compact('edition', 'ventures'));
    }
}
