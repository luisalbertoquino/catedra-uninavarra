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

        if (config('catedra.require_verified_to_pledge') && ! $request->user()->hasVerifiedEmail()) {
            return redirect(route('ventures.show', $venture).'#apoyar')->with('error', 'Verifica tu correo para registrar una intención de apoyo.');
        }

        $data = $request->validate([
            'types' => ['required', 'array', 'min:1', 'max:'.count(SupportPledge::TYPES)],
            'types.*' => [Rule::in(array_keys(SupportPledge::TYPES))],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'public_ranking' => ['nullable', 'boolean'],
        ], ['types.required' => 'Elige al menos una forma de apoyo.'], ['types' => 'formas de apoyo', 'amount' => 'monto', 'message' => 'mensaje']);
        $types = SupportPledge::normalizeTypes($data['types']);
        $monetary = (bool) array_intersect($types, SupportPledge::MONETARY);

        $user = $request->user();
        if (! empty($data['phone']) && ! $user->phone) {
            $user->update(['phone' => $data['phone']]);
        }
        // Consentimiento para aparecer en el ranking público de inversionistas
        $user->update(['show_in_investor_ranking' => $request->boolean('public_ranking')]);

        $venture->pledges()->create([
            'user_id' => $user->id,
            'type' => $types[0],
            'types' => $types,
            'amount' => $monetary ? ($data['amount'] ?? null) : null,
            'message' => $data['message'],
        ]);

        return redirect(route('ventures.show', $venture).'#apoyar')
            ->with('status', '¡Recibimos tu intención de apoyo! '.\App\Models\Setting::get('investment_contact_name').' te contactará pronto.');
    }
}
