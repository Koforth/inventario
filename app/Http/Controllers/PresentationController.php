<?php

namespace App\Http\Controllers;

use App\Models\Presentation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PresentationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('warehouse.catalog', [
            'title' => 'Presentaciones',
            'subtitle' => 'Formas en que se venden los productos',
            'routeName' => 'warehouse.presentations',
            'items' => Presentation::query()
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
        Presentation::create($this->validateData($request));

        return back()->with('success', 'Presentacion creada correctamente.');
    }

    public function update(Request $request, Presentation $presentation): RedirectResponse
    {
        $presentation->update($this->validateData($request, $presentation));

        return back()->with('success', 'Presentacion actualizada correctamente.');
    }

    public function destroy(Presentation $presentation): RedirectResponse
    {
        $presentation->delete();

        return back()->with('success', 'Presentacion eliminada correctamente.');
    }

    private function validateData(Request $request, ?Presentation $presentation = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:120', Rule::unique('presentations', 'nombre')->ignore($presentation)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
