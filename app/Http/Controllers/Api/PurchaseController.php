<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $purchases = Purchase::query()
            ->with(['supplier', 'user', 'items.product'])
            ->when($request->query('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json($purchases);
    }

    public function store(Request $request): JsonResponse
    {
        if (is_string($request->input('items'))) {
            $request->merge(['items' => json_decode((string) $request->input('items'), true) ?? []]);
        }

        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_type' => ['required', 'in:contado,credito'],
            'payment_method' => ['required', 'in:efectivo,tarjeta,transferencia'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.cost' => ['required', 'numeric', 'min:0'],
        ]);

        $purchase = DB::transaction(function () use ($data) {
            $supplier = Supplier::findOrFail((int) $data['supplier_id']);

            $subtotal = 0.0;
            $products = [];

            foreach ($data['items'] as $item) {
                $product = Product::whereKey((int) $item['product_id'])->lockForUpdate()->firstOrFail();
                $cost = (float) $item['cost'];
                $quantity = (int) $item['quantity'];

                $lineSubtotal = $cost * $quantity;
                $taxAmount = $product->taxable ? $lineSubtotal * 0.15 : 0.0;

                $subtotal += $lineSubtotal + $taxAmount;

                $products[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'cost' => $cost,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineSubtotal + $taxAmount,
                ];
            }

            $purchase = Purchase::create([
                'number' => 'COM-' . now()->format('Ymd') . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                'supplier_id' => (int) $data['supplier_id'],
                'user_id' => auth()->id(),
                'purchase_type' => $data['purchase_type'],
                'payment_method' => $data['payment_method'],
                'subtotal' => round($subtotal, 2),
                'total' => round($subtotal, 2),
            ]);

            foreach ($products as $entry) {
                $product = $entry['product'];

                $product->update([
                    'stock' => $product->stock + $entry['quantity'],
                    'purchase_price' => round($entry['cost'], 2),
                ]);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $entry['quantity'],
                    'unit_cost' => round($entry['cost'], 2),
                    'tax_amount' => round($entry['tax_amount'], 2),
                    'line_total' => round($entry['line_total'], 2),
                ]);

                Movement::create([
                    'product_id' => $product->id,
                    'tipo' => 'entrada',
                    'cantidad' => $entry['quantity'],
                    'user_id' => auth()->id(),
                ]);
            }

            return $purchase->load(['supplier', 'user', 'items.product']);
        });

        return response()->json(['message' => 'Compra registrada correctamente.', 'purchase' => $purchase], 201);
    }

    public function show(Purchase $purchase): JsonResponse
    {
        return response()->json($purchase->load(['supplier', 'user', 'items.product']));
    }
}