<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $edition = Edition::current();

        $votes = $user->votes()->with('venture.category')->latest()->get();
        $comments = $user->comments()->with('venture')->latest()->take(10)->get();
        $pledges = $user->pledges()->with('venture')->latest()->get();
        $votesLeft = $edition ? max(0, $edition->max_votes_per_user - $votes->where('edition_id', $edition->id)->count()) : 0;

        return view('account.index', compact('user', 'edition', 'votes', 'comments', 'pledges', 'votesLeft'));
    }
}
