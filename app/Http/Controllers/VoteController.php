<?php

namespace App\Http\Controllers;

use App\Models\Venture;
use App\Services\VotingGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    /** Vota o retira el voto (mientras la votación esté abierta). Responde JSON para la UI interactiva. */
    public function toggle(Request $request, Venture $venture)
    {
        $user = $request->user();
        abort_unless($venture->is_published, 404);

        if ($reason = VotingGate::denialReason($user, $venture)) {
            return $this->respond($request, $venture, false, $reason, 422);
        }

        $result = DB::transaction(function () use ($user, $venture, $request) {
            $existing = $venture->votes()->where('user_id', $user->id)->lockForUpdate()->first();
            if ($existing) {
                $existing->delete();

                return ['voted' => false, 'message' => 'Retiraste tu voto.'];
            }
            if (VotingGate::votesLeft($user, $venture) <= 0) {
                return ['error' => "Ya usaste tus {$venture->edition->max_votes_per_user} votos. Retira uno para votar por este emprendimiento."];
            }
            $venture->votes()->create([
                'user_id' => $user->id,
                'edition_id' => $venture->edition_id,
                'ip' => $request->ip(),
            ]);

            return ['voted' => true, 'message' => '¡Gracias por tu voto!'];
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
