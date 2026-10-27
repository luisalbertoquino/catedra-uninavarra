<?php

namespace App\Http\Controllers;

use App\Models\Venture;
use App\Services\VotingGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoteController extends Controller
{
    /** Caracteres mínimos del comentario obligatorio para votar */
    public const MIN_COMMENT = 15;

    /**
     * Vota o retira el voto (mientras la votación esté abierta). Responde JSON para la UI interactiva.
     * Votar es un paso a paso: primero estrellas, luego comentario y por último el voto. El voto exige
     * calificación y comentario, que se guardan junto con él (si ya opinó sobre este emprendimiento, se actualiza).
     */
    public function toggle(Request $request, Venture $venture)
    {
        $user = $request->user();
        abort_unless($venture->is_published, 404);

        if ($reason = VotingGate::denialReason($user, $venture)) {
            return $this->respond($request, $venture, false, $reason, 422);
        }

        if ($existing = $venture->votes()->where('user_id', $user->id)->first()) {
            $existing->delete();

            return $this->respond($request, $venture, false, 'Retiraste tu voto.');
        }

        try {
            $data = $request->validate([
                'rating' => ['required', 'integer', 'between:1,5'],
                'body' => ['required', 'string', 'min:'.self::MIN_COMMENT, 'max:1000'],
            ], [
                'rating.required' => 'Califica el emprendimiento con estrellas.',
                'body.required' => 'Escribe un comentario para el equipo.',
                'body.min' => 'Tu comentario debe tener al menos :min caracteres.',
            ]);
        } catch (ValidationException $e) {
            return $this->respond($request, $venture, false, collect($e->errors())->flatten()->first(), 422);
        }

        $result = DB::transaction(function () use ($user, $venture, $request, $data) {
            if (VotingGate::votesLeft($user, $venture) <= 0) {
                $max = $venture->edition->max_votes_per_user;
                if ($max === 1) {
                    $current = $user->votes()->where('edition_id', $venture->edition_id)->with('venture')->first()?->venture;

                    return ['error' => "Ya votaste por {$current?->name}. Solo hay un voto por persona: retíralo allí si quieres votar por este emprendimiento."];
                }

                return ['error' => "Ya usaste tus {$max} votos. Retira uno para votar por este emprendimiento."];
            }

            $comment = $venture->comments()->where('user_id', $user->id)->latest()->first();
            $fields = ['body' => $data['body'], 'rating' => $data['rating']];
            $comment
                ? $comment->update($fields)
                : $venture->comments()->create($fields + ['user_id' => $user->id, 'is_approved' => ! config('catedra.moderate_comments')]);

            $venture->votes()->create([
                'user_id' => $user->id,
                'edition_id' => $venture->edition_id,
                'ip' => $request->ip(),
            ]);

            return ['voted' => true, 'message' => '¡Gracias por tu voto y tu opinión!'];
        });

        if (isset($result['error'])) {
            return $this->respond($request, $venture, false, $result['error'], 422);
        }

        return $this->respond($request, $venture, $result['voted'], $result['message']);
    }

    private function respond(Request $request, Venture $venture, bool $voted, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'voted' => $voted,
                'count' => $venture->votes()->count(),
                'left' => $request->user() ? VotingGate::votesLeft($request->user(), $venture) : null,
                'message' => $message,
            ], $status);
        }

        return back()->with($status === 200 ? 'status' : 'error', $message);
    }
}
