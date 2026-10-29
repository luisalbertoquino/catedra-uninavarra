<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Edition;
use App\Models\SupportPledge;
use App\Models\Venture;
use App\Services\VotingGate;
use Illuminate\Http\Request;

class VentureController extends Controller
{
    public function index(Request $request)
    {
        $edition = $request->filled('edicion')
            ? Edition::published()->where('slug', $request->edicion)->firstOrFail()
            : Edition::current();

        $ventures = Venture::published()
            ->where('edition_id', $edition?->id)
            ->with('category')
            ->withCount(['votes', 'approvedComments as comments_count'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('tagline', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('academic_program', 'like', $term));
            })
            ->when($request->filled('categoria'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->categoria)))
            ->when($request->boolean('finalistas'), fn ($q) => $q->where('is_finalist', true));

        match ($request->get('orden', 'populares')) {
            'recientes' => $ventures->latest(),
            'az' => $ventures->orderBy('name'),
            'comentados' => $ventures->orderByDesc('comments_count'),
            default => $ventures->orderByVotes()->orderBy('name'),
        };

        $ventures = $ventures->paginate(12)->withQueryString();

        $categories = Category::whereHas('ventures', fn ($q) => $q->published()->where('edition_id', $edition?->id))->orderBy('name')->get();
        $editions = Edition::published()->orderByDesc('year')->orderByDesc('period')->get();
        $myVotes = $request->user()?->votes()->pluck('venture_id')->all() ?? [];
        $votesLeft = $request->user() && $edition
            ? max(0, $edition->max_votes_per_user - $request->user()->votes()->where('edition_id', $edition->id)->count())
            : null;

        return view('public.ventures.index', compact('edition', 'ventures', 'categories', 'editions', 'myVotes', 'votesLeft'));
    }

    public function show(Request $request, Venture $venture)
    {
        abort_unless($venture->is_published && $venture->edition->is_published, 404);

        // Contar una visita por sesión
        $seen = $request->session()->get('seen_ventures', []);
        if (! in_array($venture->id, $seen)) {
            $venture->increment('views');
            $request->session()->push('seen_ventures', $venture->id);
        }

        $venture->load(['edition', 'category', 'members', 'images'])
            ->loadCount(['votes', 'approvedComments as comments_count', 'pledges'])
            ->loadAvg('approvedComments as rating_avg', 'rating');

        $comments = $venture->approvedComments()->with('user')->paginate(8, ['*'], 'comentarios');

        $user = $request->user();
        $hasVoted = $user ? $venture->votes()->where('user_id', $user->id)->exists() : false;
        $votingBlocked = VotingGate::denialReason($user, $venture);
        $votesLeft = $user ? VotingGate::votesLeft($user, $venture) : null;
        // Opinión previa del usuario, para precargar el voto paso a paso
        $myReview = $user ? $venture->comments()->where('user_id', $user->id)->latest()->first(['rating', 'body']) : null;

        $related = Venture::published()->where('edition_id', $venture->edition_id)->whereKeyNot($venture->id)
            ->when($venture->category_id, fn ($q) => $q->orderByRaw('category_id = ? desc', [$venture->category_id]))
            ->with('category')->withCount('votes')->inRandomOrder()->take(3)->get();

        $pledgeTypes = SupportPledge::TYPES;

        return view('public.ventures.show', compact('venture', 'comments', 'hasVoted', 'votingBlocked', 'votesLeft', 'related', 'pledgeTypes', 'myReview'));
    }
}
