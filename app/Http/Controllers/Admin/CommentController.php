<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = Comment::with(['user', 'venture'])
            ->when($request->estado === 'pendientes', fn ($q) => $q->where('is_approved', false))
            ->when($request->estado === 'aprobados', fn ($q) => $q->where('is_approved', true))
            ->when($request->filled('q'), fn ($q) => $q->where('body', 'like', '%'.$request->q.'%'))
            ->latest()->paginate(25)->withQueryString();

        return view('admin.comments.index', compact('comments'));
    }

    public function toggle(Comment $comment)
    {
        $comment->update(['is_approved' => ! $comment->is_approved]);

        return back()->with('status', $comment->is_approved ? 'Comentario publicado.' : 'Comentario ocultado.');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return back()->with('status', 'Comentario eliminado.');
    }
}
