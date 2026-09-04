<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movement;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function entries(Request $request): JsonResponse
    {
        $movements = Movement::query()
            ->with(['product', 'user'])
            ->where('tipo', 'entrada')
            ->when($request->query('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json($movements);
    }

    public function storeEntry(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'nota' => ['nullable', 'string', 'max:255'],
        ]);

        $movement = DB::transaction(function () use ($data) {
            $product = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            $product->increment('stock', $data['cantidad']);

            return Movement::create([
                'product_id' => $product->id,
                'tipo' => 'entrada',
                'cantidad' => $data['cantidad'],
                'user_id' => auth()->id(),
            ]);
        });

        return response()->json([
            'message' => 'Entrada registrada correctamente.',
            'movement' => $movement->load('product'),
        ], 201);
    }

    public function kardex(Request $request): JsonResponse
    {
        $movements = Movement::query()
            ->with(['product.brand', 'product.category', 'user'])
            ->when($request->query('tipo'), fn ($q, $tipo) => $q->where('tipo', $tipo))
            ->when($request->query('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->query('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json($movements);
    }

    public function stock(): JsonResponse
    {
        return response()->json([
            'total_products' => Product::count(),
            'low_stock' => Product::whereColumn('stock', '<=', 'stock_minimo')->get(),
            'inventory_value' => (float) Product::selectRaw('COALESCE(SUM(stock * purchase_price), 0) as total')->value('total'),
        ]);
    }
}