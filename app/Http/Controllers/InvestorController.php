<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\SupportPledge;
use App\Services\InvestorRanking;

/** Página para inversionistas y aliados: el punto de llegada del embudo de apoyo. */
class InvestorController extends Controller
{
    public function index()
    {
        $edition = Edition::current();

        // Primero los que declararon cuánto necesitan, luego los más votados
        $ventures = $edition
            ? $edition->ventures()->published()->with('category')->withCount('votes')
                ->orderByRaw('funding_goal IS NULL, startup_amount IS NULL')->orderByVotes()->take(6)->get()
            : collect();

        $investors = $edition ? InvestorRanking::for($edition)->take(5) : collect();

        $stats = [
            'ventures' => $edition?->ventures()->published()->count() ?? 0,
            'pledges' => SupportPledge::count(),
            'completed' => SupportPledge::where('status', 'concretado')->count(),
        ];

        return view('public.investors', compact('edition', 'ventures', 'investors', 'stats'));
    }
}
