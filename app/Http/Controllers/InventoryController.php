<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function entries(Request $request): View
    {
        return view('inventory.entries', [
            'products' => $this->productOptions($request),
            'recentMovements' => $this->recentMovements('entrada'),
            'search' => trim((string) $request->query('search', '')),
            'stats' => [
                'today_entries' => Movement::where('tipo', 'entrada')->whereDate('created_at', today())->sum('cantidad'),
                'low_stock' => Product::whereColumn('stock', '<=', 'stock_minimo')->count(),
                'total_products' => Product::count(),
            ],
        ]);
    }

    public function sales(Request $request): View
    {
        return view('inventory.sales', [
            'products' => $this->productOptions($request, onlyAvailable: true),
            'recentMovements' => $this->recentMovements('venta'),
            'search' => trim((string) $request->query('search', '')),
            'stats' => [
                'today_sales' => Movement::where('tipo', 'venta')->whereDate('created_at', today())->sum('cantidad'),
                'available_products' => Product::where('stock', '>', 0)->count(),
                'low_stock' => Product::whereColumn('stock', '<=', 'stock_minimo')->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $movement = DB::transaction(function () use ($data) {
            $product = Product::whereKey($data['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $product->increment('stock', $data['cantidad']);

            return Movement::create([
                'product_id' => $product->id,
                'tipo' => 'entrada',
                'cantidad' => $data['cantidad'],
                'user_id' => auth()->id(),
            ]);
        });

        return $this->inventoryResponse(
            $request,
            'Entrada de inventario registrada correctamente.',
            $movement
        );
    }

    public function processSale(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $movement = DB::transaction(function () use ($data) {
            $product = Product::whereKey($data['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($product->stock < $data['cantidad']) {
                throw ValidationException::withMessages([
                    'cantidad' => 'No hay stock suficiente para procesar esta venta.',
                ]);
            }

            $product->decrement('stock', $data['cantidad']);

            return Movement::create([
                'product_id' => $product->id,
                'tipo' => 'venta',
                'cantidad' => $data['cantidad'],
                'user_id' => auth()->id(),
            ]);
        });

        return $this->inventoryResponse(
            $request,
            'Venta procesada y stock descontado correctamente.',
            $movement
        );
    }

    private function inventoryResponse(Request $request, string $message, Movement $movement): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'movement' => $movement->load('product'),
            ], 201);
        }

        return back()->with('success', $message);
    }

    private function productOptions(Request $request, bool $onlyAvailable = false)
    {
        $search = trim((string) $request->query('search', ''));

        return Product::query()
            ->with(['brand', 'category'])
            ->when($onlyAvailable, fn ($query) => $query->where('stock', '>', 0))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhereHas('brand', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('category', fn ($query) => $query->where('nombre', 'like', "%{$search}%"));
                });
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();
    }

    private function recentMovements(string $type)
    {
        return Movement::query()
            ->with(['product', 'user'])
            ->where('tipo', $type)
            ->latest()
            ->limit(6)
            ->get();
    }
}
