<?php

namespace App\Http\Controllers;

use App\Models\SupportPledge;
use App\Models\Venture;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PledgeController extends Controller
{
    public function store(Request $request, Venture $venture)
    {
        abort_unless($venture->is_published, 404);

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(SupportPledge::TYPES))],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:30'],
        ], [], ['type' => 'tipo de apoyo', 'amount' => 'monto', 'message' => 'mensaje']);

        $user = $request->user();
        if (! empty($data['phone']) && ! $user->phone) {
            $user->update(['phone' => $data['phone']]);
        }

        $venture->pledges()->create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'amount' => $data['amount'] ?? null,
            'message' => $data['message'],
        ]);

        return redirect(route('ventures.show', $venture).'#apoyar')
            ->with('status', '¡Recibimos tu intención de apoyo! El equipo de la Cátedra te contactará pronto.');
    }
}
