<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Services\Ranking;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $edition = $request->filled('edicion')
            ? Edition::published()->where('slug', $request->edicion)->firstOrFail()
            : Edition::current();

        $ranking = $edition ? Ranking::for($edition) : collect();

        // Antes de publicar resultados, el ranking público es solo por votos de la comunidad
        if ($edition && ! $edition->results_published) {
            $ranking = $ranking->sortByDesc('votes_count')->values();
        }

        $editions = Edition::published()->orderByDesc('year')->orderByDesc('period')->get();
        $totalVotes = $ranking->sum('votes_count');

        return view('public.ranking', compact('edition', 'ranking', 'editions', 'totalVotes'));
    }
}
