<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Edition;
use App\Models\Venture;
use App\Models\VentureImage;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VentureController extends Controller
{
    public function index(Request $request)
    {
        $editions = Edition::orderByDesc('year')->orderByDesc('period')->get();
        $editionId = $request->input('edicion', Edition::current()?->id);

        $ventures = Venture::with(['category', 'edition'])
            ->withCount(['votes', 'comments', 'pledges', 'members'])
            ->when($editionId, fn ($q) => $q->where('edition_id', $editionId))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.ventures.index', compact('ventures', 'editions', 'editionId'));
    }

    public function create(Request $request)
    {
        $venture = new Venture(['edition_id' => $request->input('edicion', Edition::current()?->id), 'is_published' => true]);

        return $this->form($venture);
    }

    public function store(Request $request)
    {
        $venture = DB::transaction(function () use ($request) {
            $venture = Venture::create($this->validated($request));
            $this->syncRelations($request, $venture);

            return $venture;
        });

        return redirect()->route('admin.ventures.edit', $venture)->with('status', 'Emprendimiento creado.');
    }

    public function edit(Venture $venture)
    {
        $venture->load(['members', 'images']);

        return $this->form($venture);
    }

    public function update(Request $request, Venture $venture)
    {
        DB::transaction(function () use ($request, $venture) {
            $venture->update($this->validated($request, $venture));
            $this->syncRelations($request, $venture);
        });

        return back()->with('status', 'Emprendimiento actualizado.');
    }

    public function destroy(Venture $venture)
    {
        Media::delete($venture->logo);
        Media::delete($venture->cover);
        $venture->images->each(fn ($i) => Media::delete($i->path));
        $venture->members->each(fn ($m) => Media::delete($m->photo));
        $venture->delete();

        return redirect()->route('admin.ventures.index', ['edicion' => $venture->edition_id])->with('status', 'Emprendimiento eliminado.');
    }

    public function destroyImage(VentureImage $image)
    {
        Media::delete($image->path);
        $image->delete();

        return back()->with('status', 'Imagen eliminada.');
    }

    private function form(Venture $venture)
    {
        $editions = Edition::orderByDesc('year')->orderByDesc('period')->get();
        $categories = Category::orderBy('name')->get();
        $members = $venture->exists
            ? $venture->members->map(fn ($m) => $m->only('id', 'name', 'role', 'academic_program', 'linkedin') + ['photo_url' => $m->photo_url])->all()
            : [];

        return view('admin.ventures.form', compact('venture', 'editions', 'categories', 'members'));
    }

    private function validated(Request $request, ?Venture $venture = null): array
    {
        $data = $request->validate([
            'edition_id' => ['required', 'exists:editions,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'alpha_dash', 'max:160', Rule::unique('ventures')->ignore($venture)],
            'tagline' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'problem' => ['nullable', 'string', 'max:5000'],
            'solution' => ['nullable', 'string', 'max:5000'],
            'target_market' => ['nullable', 'string', 'max:5000'],
            'mission' => ['nullable', 'string', 'max:5000'],
            'vision' => ['nullable', 'string', 'max:5000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'cover' => ['nullable', 'image', 'max:4096'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'academic_program' => ['nullable', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'tiktok' => ['nullable', 'url', 'max:255'],
            'funding_goal' => ['nullable', 'numeric', 'min:0'],
            'startup_amount' => ['nullable', 'numeric', 'min:0'],
            'team_group' => ['nullable', 'string', 'max:40'],
            'gallery' => ['array', 'max:12'],
            'gallery.*' => ['image', 'max:4096'],
            'members' => ['array', 'max:10'],
            'members.*.name' => ['required', 'string', 'max:150'],
            'members.*.role' => ['nullable', 'string', 'max:100'],
            'members.*.academic_program' => ['nullable', 'string', 'max:150'],
            'members.*.linkedin' => ['nullable', 'url', 'max:255'],
            'members.*.photo' => ['nullable', 'image', 'max:2048'],
        ], [], ['members.*.name' => 'nombre del integrante', 'gallery.*' => 'imagen de galería']);

        $base = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $slug = $base;
        $i = 2;
        while (Venture::where('slug', $slug)->when($venture, fn ($q) => $q->whereKeyNot($venture->id))->exists()) {
            $slug = $base.'-'.$i++;
        }
        $data['slug'] = $slug;
        $data['is_published'] = $request->boolean('is_published');
        $data['logo'] = Media::store($request->file('logo'), 'ventures/logos', $venture?->logo);
        $data['cover'] = Media::store($request->file('cover'), 'ventures/covers', $venture?->cover);
        unset($data['gallery'], $data['members']);

        return $data;
    }

    private function syncRelations(Request $request, Venture $venture): void
    {
        // Galería: se agregan las nuevas imágenes
        $next = (int) $venture->images()->max('sort');
        foreach ($request->file('gallery', []) as $file) {
            $venture->images()->create(['path' => Media::store($file, 'ventures/gallery'), 'sort' => ++$next]);
        }

        // Equipo: se conservan fotos de integrantes existentes
        $keep = [];
        foreach (array_values($request->input('members', [])) as $i => $row) {
            $member = isset($row['id']) ? $venture->members()->find($row['id']) : null;
            $member ??= $venture->members()->make();
            $member->fill([
                'name' => $row['name'],
                'role' => $row['role'] ?? null,
                'academic_program' => $row['academic_program'] ?? null,
                'linkedin' => $row['linkedin'] ?? null,
                'sort' => $i,
            ]);
            $originalKey = array_keys($request->input('members', []))[$i];
            if ($photo = $request->file("members.$originalKey.photo")) {
                $member->photo = Media::store($photo, 'ventures/team', $member->photo);
            }
            $member->save();
            $keep[] = $member->id;
        }
        $venture->members()->whereNotIn('id', $keep)->get()->each(function ($m) {
            Media::delete($m->photo);
            $m->delete();
        });
    }
}
