<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('warehouse.catalog', [
            'title' => 'Marcas',
            'subtitle' => 'Marcas de productos',
            'routeName' => 'warehouse.brands',
            'items' => Brand::query()
                ->when($search !== '', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
                ->orderBy('nombre')
                ->paginate(10)
                ->withQueryString(),
            'search' => $search,
            'descriptionField' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Brand::create($this->validateData($request));

        return back()->with('success', 'Marca creada correctamente.');
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $brand->update($this->validateData($request, $brand));

        return back()->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->delete();

        return back()->with('success', 'Marca eliminada correctamente.');
    }

    private function validateData(Request $request, ?Brand $brand = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:120', Rule::unique('brands', 'nombre')->ignore($brand)],
        ]);
    }
}
