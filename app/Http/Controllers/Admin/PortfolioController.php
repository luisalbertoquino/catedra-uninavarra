<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Portafolios de cada convocatoria (enlace web y/o PDF). El destacado es el del botón flotante del sitio. */
class PortfolioController extends Controller
{
    public function index()
    {
        return view('admin.portfolios.index', ['portfolios' => Portfolio::ordered()->get()]);
    }

    public function create()
    {
        return view('admin.portfolios.form', ['portfolio' => new Portfolio(['is_published' => true, 'sort' => Portfolio::max('sort') + 1])]);
    }

    public function store(Request $request)
    {
        DB::transaction(fn () => $this->featureOnly(Portfolio::create($this->validated($request))));

        return redirect()->route('admin.portfolios.index')->with('status', 'Portafolio agregado.');
    }

    public function edit(Portfolio $portfolio)
    {
        return view('admin.portfolios.form', compact('portfolio'));
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        DB::transaction(function () use ($request, $portfolio) {
            $portfolio->update($this->validated($request, $portfolio));
            $this->featureOnly($portfolio);
        });

        return redirect()->route('admin.portfolios.index')->with('status', 'Portafolio actualizado.');
    }

    public function destroy(Portfolio $portfolio)
    {
        Media::delete($portfolio->pdf);
        $portfolio->delete();

        return back()->with('status', 'Portafolio eliminado.');
    }

    /** Solo un portafolio va en el botón flotante. */
    private function featureOnly(Portfolio $portfolio): void
    {
        if ($portfolio->is_featured) {
            Portfolio::whereKeyNot($portfolio->id)->update(['is_featured' => false]);
        }
    }

    private function validated(Request $request, ?Portfolio $portfolio = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'period' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:500'],
            'url' => ['nullable', 'url', 'max:255'],
            'sort' => ['nullable', 'integer', 'between:0,999'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:30720'],
        ], ['pdf_file.max' => 'El PDF no puede pesar más de 30 MB.'], ['title' => 'título', 'period' => 'periodo', 'url' => 'enlace web', 'pdf_file' => 'PDF']);

        $keepsPdf = $request->hasFile('pdf_file') || ($portfolio?->pdf && ! $request->boolean('remove_pdf'));
        if (blank($data['url'] ?? null) && ! $keepsPdf) {
            throw \Illuminate\Validation\ValidationException::withMessages(['url' => 'Agrega el enlace web o sube el PDF.']);
        }

        $data['url'] = $data['url'] ?? null;
        $data['sort'] = (int) ($data['sort'] ?? 0);
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');
        unset($data['pdf_file']);
        if ($request->boolean('remove_pdf') && ! $request->hasFile('pdf_file')) {
            Media::delete($portfolio?->pdf);
            $data['pdf'] = null;
        } else {
            $data['pdf'] = Media::store($request->file('pdf_file'), 'portfolios', $portfolio?->pdf);
        }

        return $data;
    }
}
