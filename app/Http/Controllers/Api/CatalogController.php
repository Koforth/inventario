<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Presentation;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function categories(): JsonResponse
    {
        return response()->json(Category::withCount('products')->orderBy('nombre')->get());
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        return response()->json(Category::create($data), 201);
    }

    public function updateCategory(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        $category->update($data);

        return response()->json($category);
    }

    public function destroyCategory(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Categoria eliminada.']);
    }

    public function brands(): JsonResponse
    {
        return response()->json(Brand::withCount('products')->orderBy('nombre')->get());
    }

    public function storeBrand(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        return response()->json(Brand::create($data), 201);
    }

    public function updateBrand(Request $request, Brand $brand): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255'],
        ]);

        $brand->update($data);

        return response()->json($brand);
    }

    public function destroyBrand(Brand $brand): JsonResponse
    {
        $brand->delete();

        return response()->json(['message' => 'Marca eliminada.']);
    }

    public function presentations(): JsonResponse
    {
        return response()->json(Presentation::orderBy('nombre')->get());
    }

    public function storePresentation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        return response()->json(Presentation::create($data), 201);
    }

    public function updatePresentation(Request $request, Presentation $presentation): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255'],
        ]);

        $presentation->update($data);

        return response()->json($presentation);
    }

    public function destroyPresentation(Presentation $presentation): JsonResponse
    {
        $presentation->delete();

        return response()->json(['message' => 'Presentacion eliminada.']);
    }

    public function customers(): JsonResponse
    {
        return response()->json(Customer::where('is_active', true)->orderBy('name')->get());
    }

    public function storeCustomer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'customer_type' => ['required', 'in:cliente,empresa'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20', 'unique:customers,dni'],
            'ruc' => ['nullable', 'string', 'max:20', 'unique:customers,ruc'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:120'],
            'business_line' => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return response()->json(Customer::create($data), 201);
    }

    public function updateCustomer(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'customer_type' => ['sometimes', 'in:cliente,empresa'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20', 'unique:customers,dni,' . $customer->id],
            'ruc' => ['nullable', 'string', 'max:20', 'unique:customers,ruc,' . $customer->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:120'],
            'business_line' => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $customer->update($data);

        return response()->json($customer);
    }

    public function suppliers(): JsonResponse
    {
        return response()->json(Supplier::withCount('products')->orderBy('name')->get());
    }

    public function storeSupplier(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20'],
            'ruc' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(Supplier::create($data), 201);
    }

    public function updateSupplier(Request $request, Supplier $supplier): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20'],
            'ruc' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $supplier->update($data);

        return response()->json($supplier);
    }

    public function destroySupplier(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return response()->json(['message' => 'Proveedor eliminado.']);
    }
}