<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'user_id' => ['required', 'exists:users,id'],
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
                'user_id' => $data['user_id'],
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
            'user_id' => ['required', 'exists:users,id'],
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
                'user_id' => $data['user_id'],
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
}
