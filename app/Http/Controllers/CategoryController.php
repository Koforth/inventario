<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('warehouse.catalog', [
            'title' => 'Categorias',
            'subtitle' => 'Clasificacion de productos',
            'routeName' => 'warehouse.categories',
            'items' => Category::query()
                ->when($search !== '', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
                ->orderBy('nombre')
                ->paginate(10)
                ->withQueryString(),
            'search' => $search,
            'descriptionField' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validateData($request));

        return back()->with('success', 'Categoria creada correctamente.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validateData($request, $category));

        return back()->with('success', 'Categoria actualizada correctamente.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Categoria eliminada correctamente.');
    }

    private function validateData(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:120', Rule::unique('categories', 'nombre')->ignore($category)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
