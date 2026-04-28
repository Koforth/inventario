<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
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
            'metodo_pago' => ['required', 'in:efectivo,tarjeta,transferencia'],
            'comprobante' => ['required', 'in:ticket,factura'],
            'efectivo_recibido' => ['required_if:metodo_pago,efectivo', 'nullable', 'numeric', 'min:0'],
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
            'metodo_pago' => ['required', 'in:efectivo,tarjeta,transferencia'],
            'comprobante' => ['required', 'in:ticket,factura'],
            'efectivo_recibido' => ['required_if:metodo_pago,efectivo', 'nullable', 'numeric', 'min:0'],
        ]);

        $movement = DB::transaction(function () use ($data) {
            $cashRegister = CashRegister::where('status', 'open')
                ->lockForUpdate()
                ->latest('opened_at')
                ->first();

            if (! $cashRegister) {
                throw ValidationException::withMessages([
                    'caja' => 'Debes abrir caja antes de procesar ventas.',
                ]);
            }

            $product = Product::whereKey($data['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($product->stock < $data['cantidad']) {
                throw ValidationException::withMessages([
                    'cantidad' => 'No hay stock suficiente para procesar esta venta.',
                ]);
            }

            $total = (float) $product->sale_price_1 * (int) $data['cantidad'];

            if ($data['metodo_pago'] === 'efectivo' && (float) $data['efectivo_recibido'] < $total) {
                throw ValidationException::withMessages([
                    'efectivo_recibido' => 'El efectivo recibido no cubre el total de la venta.',
                ]);
            }

            $product->decrement('stock', $data['cantidad']);

            $movement = Movement::create([
                'product_id' => $product->id,
                'tipo' => 'venta',
                'cantidad' => $data['cantidad'],
                'user_id' => auth()->id(),
            ]);

            $cashRegister->movements()->create([
                'type' => 'sale',
                'amount' => $total,
                'description' => "Venta {$data['comprobante']} - {$product->nombre}",
                'reference_type' => Movement::class,
                'reference_id' => $movement->id,
                'user_id' => auth()->id(),
            ]);

            return $movement;
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
            ->with(['brand', 'category', 'presentation', 'suppliers'])
            ->when($onlyAvailable, fn ($query) => $query->where('stock', '>', 0))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('proveedor', 'like', "%{$search}%")
                        ->orWhereHas('brand', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('category', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('suppliers', fn ($query) => $query->where('name', 'like', "%{$search}%"));
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
