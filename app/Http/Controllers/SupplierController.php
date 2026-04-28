<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('purchases.suppliers', [
            'suppliers' => Supplier::query()
                ->when($search !== '', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('dni', 'like', "%{$search}%")
                        ->orWhere('ruc', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Supplier::create($this->validateData($request));

        return back()->with('success', 'Proveedor creado correctamente.');
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validateData($request, $supplier));

        return back()->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return back()->with('success', 'Proveedor eliminado correctamente.');
    }

    private function validateData(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160', Rule::unique('suppliers', 'name')->ignore($supplier)],
            'dni' => ['nullable', 'string', 'max:50'],
            'ruc' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:50'],
            'contact_name' => ['nullable', 'string', 'max:160'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
