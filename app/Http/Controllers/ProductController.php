<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', 'all');

        $products = Product::query()
            ->with(['category', 'brand'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhere('proveedor', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('brand', fn ($query) => $query->where('nombre', 'like', "%{$search}%"));
                });
            })
            ->when($status === 'low', fn ($query) => $query->whereColumn('stock', '<=', 'stock_minimo'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Product::count(),
            'low_stock' => Product::whereColumn('stock', '<=', 'stock_minimo')->count(),
            'inventory_value' => Product::selectRaw('COALESCE(SUM(stock * precio), 0) as total')->value('total'),
        ];

        return view('products.index', compact('products', 'search', 'status', 'stats'));
    }

    public function create(): View
    {
        return view('products.create', [
            'product' => new Product(['stock' => 0, 'stock_minimo' => 0, 'precio' => 0]),
            'categories' => Category::orderBy('nombre')->get(),
            'brands' => Brand::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedProduct($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Product $product): View
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('nombre')->get(),
            'brands' => Brand::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedProduct($request, $product);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    private function validatedProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($product)],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'proveedor' => ['nullable', 'string', 'max:160'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg', 'max:2048'],
        ]);
    }
}
