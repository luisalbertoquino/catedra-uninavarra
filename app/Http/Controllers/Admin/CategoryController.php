<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('ventures')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request));

        return back()->with('status', 'Categoría creada.');
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));

        return back()->with('status', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('status', 'Categoría eliminada. Sus emprendimientos quedaron sin categoría.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'color' => ['required', 'regex:/^#[0-9a-f]{6}$/i'],
            'icon' => ['nullable', 'string', 'max:16'],
        ]);
        $data['slug'] = Str::slug($data['name']);
        validator($data, ['slug' => 'unique:categories,slug'.($category ? ','.$category->id : '')], ['slug.unique' => 'Ya existe una categoría con ese nombre.'])->validate();

        return $data;
    }
}
