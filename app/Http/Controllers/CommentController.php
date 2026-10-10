<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Venture;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Venture $venture)
    {
        abort_unless($venture->is_published, 404);

        if (config('catedra.require_verified_to_comment') && ! $request->user()->hasVerifiedEmail()) {
            return back()->with('error', 'Verifica tu correo para poder comentar.');
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:1000'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $moderated = (bool) config('catedra.moderate_comments');

        $venture->comments()->create($data + [
            'user_id' => $request->user()->id,
            'is_approved' => ! $moderated,
        ]);

        return redirect(route('ventures.show', $venture).'#opiniones')
            ->with('status', $moderated ? 'Tu opinión fue enviada y será publicada tras revisión.' : '¡Gracias por tu opinión!');
    }

    public function destroy(Request $request, Comment $comment)
    {
        abort_unless($comment->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
        $comment->delete();

        return back()->with('status', 'Comentario eliminado.');
    }
}
