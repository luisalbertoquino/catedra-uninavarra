<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Services\PortfolioImporter;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function create()
    {
        $editions = Edition::orderByDesc('year')->orderByDesc('period')->get();

        return view('admin.import.create', ['editions' => $editions, 'current' => Edition::current()]);
    }

    public function store(Request $request, PortfolioImporter $importer)
    {
        $data = $request->validate([
            'edition_id' => ['required', 'exists:editions,id'],
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
            'logos' => ['array', 'max:60'],
            'logos.*' => ['image', 'max:4096'],
        ], [], ['file' => 'archivo de Excel', 'logos.*' => 'logo']);

        $edition = Edition::findOrFail($data['edition_id']);

        try {
            $ventures = $importer->parse($request->file('file')->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo leer el Excel: '.$e->getMessage());
        }
        if (! $ventures) {
            return back()->with('error', 'El Excel no tiene emprendimientos con "Nombre de la Idea de Negocio".');
        }

        $report = $importer->import($edition, $ventures, $request->file('logos', []), $request->boolean('publish', true));

        return redirect()->route('admin.import.create')
            ->with('status', count($report['created']).' emprendimientos creados y '.count($report['updated']).' actualizados.')
            ->with('report', $report + ['edition' => $edition->name]);
    }
}
