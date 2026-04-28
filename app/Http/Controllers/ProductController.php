<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Presentation;
use App\Models\Product;
use App\Models\Supplier;
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
            ->with(['category', 'brand', 'presentation', 'images', 'suppliers'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhere('proveedor', 'like', "%{$search}%")
                        ->orWhereHas('suppliers', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('category', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('brand', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('presentation', fn ($query) => $query->where('nombre', 'like', "%{$search}%"));
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
            'product' => new Product([
                'stock' => 0,
                'stock_minimo' => 0,
                'precio' => 0,
                'purchase_price' => 0,
                'sale_price_1' => 0,
                'inventoryable' => true,
            ]),
            'categories' => Category::orderBy('nombre')->get(),
            'brands' => Brand::orderBy('nombre')->get(),
            'presentations' => Presentation::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedProduct($request);

        $images = array_filter((array) $request->file('images', []));
        $supplierNames = $this->parseSupplierNames($data['proveedor'] ?? '');
        unset($data['images']);
        $this->normalizeProductData($data);

        $product = Product::create($data);
        $this->syncSuppliers($product, $supplierNames);
        $this->storeImages($product, $images);

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Product $product): View
    {
        return view('products.show', [
            'product' => $product->load(['brand', 'category', 'presentation', 'images', 'suppliers']),
        ]);
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product->load(['images', 'suppliers', 'presentation']),
            'categories' => Category::orderBy('nombre')->get(),
            'brands' => Brand::orderBy('nombre')->get(),
            'presentations' => Presentation::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedProduct($request, $product);

        $images = array_filter((array) $request->file('images', []));
        $supplierNames = $this->parseSupplierNames($data['proveedor'] ?? '');
        unset($data['images']);
        $this->normalizeProductData($data);

        $product->update($data);
        $this->syncSuppliers($product, $supplierNames);
        $this->storeImages($product, $images);

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $paths = $product->images()->pluck('path')->all();

        if ($product->image_path && ! in_array($product->image_path, $paths, true)) {
            $paths[] = $product->image_path;
        }

        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
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
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'sale_price_1' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'sale_price_2' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'sale_price_3' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
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
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg', 'max:2048'],
        ]);
    }

    private function storeImages(Product $product, array $images): void
    {
        if ($images === []) {
            return;
        }

        $nextSortOrder = (int) $product->images()->max('sort_order') + 1;
        $firstPath = $product->image_path;

        foreach ($images as $image) {
            $path = $image->store('products', 'public');

            $product->images()->create([
                'path' => $path,
                'sort_order' => $nextSortOrder++,
            ]);

            $firstPath ??= $path;
        }

        if ($product->image_path !== $firstPath) {
            $product->update(['image_path' => $firstPath]);
        }
    }

    private function parseSupplierNames(?string $supplierList): array
    {
        return collect(explode(',', (string) $supplierList))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->values()
            ->all();
    }

    private function syncSuppliers(Product $product, array $supplierNames): void
    {
        $supplierIds = collect($supplierNames)
            ->map(fn ($name) => Supplier::firstOrCreate(['name' => $name])->id)
            ->all();

        $product->suppliers()->sync($supplierIds);
    }

    private function normalizeProductData(array &$data): void
    {
        $data['taxable'] = (bool) ($data['taxable'] ?? false);
        $data['perishable'] = (bool) ($data['perishable'] ?? false);
        $data['inventoryable'] = (bool) ($data['inventoryable'] ?? false);
        $data['precio'] = $data['sale_price_1'];

        if (! $data['perishable']) {
            $data['expires_at'] = null;
        }
    }
}
