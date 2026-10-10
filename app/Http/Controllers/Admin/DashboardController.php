<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Edition;
use App\Models\SupportPledge;
use App\Models\User;
use App\Models\Vote;
use App\Services\Ranking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $editions = Edition::orderByDesc('year')->orderByDesc('period')->get();
        $edition = $request->filled('edicion')
            ? $editions->firstWhere('id', (int) $request->edicion)
            : (Edition::current() ?? $editions->first());

        $ventureIds = $edition?->ventures()->pluck('id') ?? collect();

        $kpis = [
            'ventures' => $ventureIds->count(),
            'votes' => Vote::where('edition_id', $edition?->id)->count(),
            'voters' => Vote::where('edition_id', $edition?->id)->distinct('user_id')->count('user_id'),
            'users' => User::count(),
            'investors' => User::where('role', 'inversionista')->count(),
            'comments' => Comment::whereIn('venture_id', $ventureIds)->count(),
            'pending_comments' => Comment::where('is_approved', false)->count(),
            'pledges' => SupportPledge::whereIn('venture_id', $ventureIds)->count(),
            'pledged_amount' => (float) SupportPledge::whereIn('venture_id', $ventureIds)->whereIn('type', ['inversion', 'donacion'])->sum('amount'),
            'pending_pledges' => SupportPledge::where('status', 'pendiente')->count(),
        ];

        // Votos por día (últimos 30 días de la edición)
        $votesByDay = Vote::where('edition_id', $edition?->id)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')->orderBy('day')->get()
            ->mapWithKeys(fn ($r) => [$r->day => (int) $r->total]);

        $ranking = $edition ? Ranking::for($edition, false)->sortByDesc('votes_count')->take(8)->values() : collect();

        $pledgesByType = SupportPledge::whereIn('venture_id', $ventureIds)
            ->selectRaw('type, COUNT(*) as total')->groupBy('type')->pluck('total', 'type');

        $latestPledges = SupportPledge::with(['user', 'venture'])->latest()->take(6)->get();
        $latestComments = Comment::with(['user', 'venture'])->latest()->take(6)->get();

        return view('admin.dashboard', compact('editions', 'edition', 'kpis', 'votesByDay', 'ranking', 'pledgesByType', 'latestPledges', 'latestComments'));
    }
}
