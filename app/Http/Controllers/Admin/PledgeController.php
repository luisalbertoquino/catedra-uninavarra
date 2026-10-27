<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\SupportPledge;
use App\Support\Csv;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PledgeController extends Controller
{
    public function index(Request $request)
    {
        $pledges = $this->query($request)->paginate(20)->withQueryString();
        $editions = Edition::orderByDesc('year')->orderByDesc('period')->get();
        $counts = SupportPledge::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.pledges.index', compact('pledges', 'editions', 'counts'));
    }

    public function update(Request $request, SupportPledge $pledge)
    {
        $pledge->update($request->validate([
            'status' => ['required', Rule::in(array_keys(SupportPledge::STATUSES))],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]));

        return back()->with('status', 'Intención de apoyo actualizada.');
    }

    public function export(Request $request)
    {
        $rows = $this->query($request)->get()->map(fn ($p) => [
            $p->created_at->format('Y-m-d H:i'), $p->venture->name, $p->type_label, $p->amount,
            $p->user->name, $p->user->email, $p->user->phone, $p->user->organization,
            $p->message, $p->status_label, $p->admin_notes,
        ]);

        return Csv::download('intenciones-de-apoyo.csv',
            ['Fecha', 'Emprendimiento', 'Tipo', 'Monto', 'Nombre', 'Correo', 'Teléfono', 'Organización', 'Mensaje', 'Estado', 'Notas'],
            $rows);
    }

    private function query(Request $request)
    {
        return SupportPledge::with(['user', 'venture.edition'])
            ->when($request->filled('estado'), fn ($q) => $q->where('status', $request->estado))
            ->when($request->filled('tipo'), fn ($q) => $q->whereJsonContains('types', (string) $request->tipo))
            ->when($request->filled('edicion'), fn ($q) => $q->whereHas('venture', fn ($v) => $v->where('edition_id', $request->edicion)))
            ->latest();
    }
}
