<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');

        $products = Product::query()
            ->with(['category', 'brand', 'presentation', 'prices', 'suppliers'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('brand', fn ($q) => $q->where('nombre', 'like', "%{$search}%"));
                });
            })
            ->when($status === 'low', fn ($query) => $query->whereColumn('stock', '<=', 'stock_minimo'))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'price_1' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'price_1_label' => ['nullable', 'string', 'max:80'],
            'price_2' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'price_2_label' => ['nullable', 'string', 'max:80'],
            'price_3' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'price_3_label' => ['nullable', 'string', 'max:80'],
            'stock' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'proveedor' => ['nullable', 'string', 'max:160'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'presentation_id' => ['required', 'exists:presentations,id'],
            'taxable' => ['nullable', 'boolean'],
            'perishable' => ['nullable', 'boolean'],
            'inventoryable' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $product = Product::create([
            ...$data,
            'precio' => $data['price_1'],
            'sale_price_1' => $data['price_1'],
            'sale_price_2' => $data['price_2'] ?? null,
            'sale_price_3' => $data['price_3'] ?? null,
            'taxable' => (bool) ($data['taxable'] ?? false),
            'perishable' => (bool) ($data['perishable'] ?? false),
            'inventoryable' => (bool) ($data['inventoryable'] ?? true),
        ]);

        $prices = [
            ['label' => $data['price_1_label'] ?? 'Precio 1', 'amount' => $data['price_1'], 'sort_order' => 1, 'is_active' => true],
        ];
        if (isset($data['price_2'])) {
            $prices[] = ['label' => $data['price_2_label'] ?? 'Precio 2', 'amount' => $data['price_2'], 'sort_order' => 2, 'is_active' => true];
        }
        if (isset($data['price_3'])) {
            $prices[] = ['label' => $data['price_3_label'] ?? 'Precio 3', 'amount' => $data['price_3'], 'sort_order' => 3, 'is_active' => true];
        }
        foreach ($prices as $price) {
            $product->prices()->create($price);
        }

        if (! empty($data['proveedor'])) {
            $supplier = Supplier::firstOrCreate(['name' => trim($data['proveedor'])]);
            $product->suppliers()->attach($supplier);
        }

        return response()->json(['message' => 'Producto creado.', 'product' => $product->load('prices')], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product->load(['category', 'brand', 'presentation', 'prices', 'suppliers', 'images']));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode,' . $product->id],
            'nombre' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'price_1' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'price_2' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'price_3' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'proveedor' => ['nullable', 'string', 'max:160'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'presentation_id' => ['nullable', 'exists:presentations,id'],
            'taxable' => ['nullable', 'boolean'],
            'perishable' => ['nullable', 'boolean'],
            'inventoryable' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        if (array_key_exists('price_1', $data)) {
            $data['precio'] = $data['price_1'];
            $data['sale_price_1'] = $data['price_1'];
        }

        $product->update($data);

        return response()->json(['message' => 'Producto actualizado.', 'product' => $product->fresh(['prices', 'category', 'brand'])]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Producto eliminado.']);
    }

    public function categories(): JsonResponse
    {
        return response()->json(Category::orderBy('nombre')->get());
    }
}