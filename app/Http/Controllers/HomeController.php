<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Edition;
use App\Models\SupportPledge;
use App\Models\User;
use App\Models\Vote;

class HomeController extends Controller
{
    public function index()
    {
        $edition = Edition::current()?->load('milestones');

        $featured = $edition
            ? $edition->ventures()->published()->with('category')->withCount('votes')
                ->orderByDesc('is_finalist')->orderByVotes()->take(6)->get()
            : collect();

        $stats = [
            'ventures' => $edition?->ventures()->published()->count() ?? 0,
            'votes' => $edition ? Vote::where('edition_id', $edition->id)->count() : 0,
            'community' => User::count(),
            'pledges' => SupportPledge::count(),
        ];

        $categories = Category::whereHas('ventures', fn ($q) => $q->published()->where('edition_id', $edition?->id))
            ->withCount(['ventures' => fn ($q) => $q->published()->where('edition_id', $edition?->id)])->get();

        $pastEditions = Edition::published()->when($edition, fn ($q) => $q->whereKeyNot($edition->id))
            ->withCount('ventures')->orderByDesc('year')->orderByDesc('period')->take(3)->get();

        return view('public.home', compact('edition', 'featured', 'stats', 'categories', 'pastEditions'));
    }
}
