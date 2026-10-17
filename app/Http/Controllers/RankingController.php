<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Services\InvestorRanking;
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

        $tab = $request->get('vista') === 'inversionistas' ? 'inversionistas' : 'emprendimientos';
        $investors = $edition ? InvestorRanking::for($edition) : collect();
        $hiddenInvestors = $edition ? InvestorRanking::hiddenCount($edition) : 0;

        return view('public.ranking', compact('edition', 'ranking', 'editions', 'totalVotes', 'tab', 'investors', 'hiddenInvestors'));
    }
}
