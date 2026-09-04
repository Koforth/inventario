<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layaway;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LayawayController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $layaways = Layaway::query()
            ->with(['customer', 'items.product'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json($layaways);
    }

    public function store(Request $request): JsonResponse
    {
        if (is_string($request->input('items'))) {
            $request->merge(['items' => json_decode((string) $request->input('items'), true) ?? []]);
        }

        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'down_payment' => ['required', 'numeric', 'min:0'],
            'pickup_at' => ['nullable', 'date'],
        ]);

        $layaway = DB::transaction(function () use ($data) {
            $items = [];
            $total = 0.0;

            foreach ($data['items'] as $item) {
                $product = Product::whereKey((int) $item['product_id'])->lockForUpdate()->firstOrFail();
                $quantity = (int) $item['quantity'];

                if ($product->inventoryable && $product->stock < $quantity) {
                    throw ValidationException::withMessages(['items' => "No hay stock suficiente para {$product->nombre}."]);
                }

                if ($product->inventoryable) {
                    $product->decrement('stock', $quantity);
                }

                $lineTotal = $product->primaryPrice() * $quantity;
                $total += $lineTotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ];
            }

            $layaway = Layaway::create([
                'number' => 'APA-' . now()->format('Ymd-His') . '-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT),
                'customer_id' => (int) $data['customer_id'],
                'user_id' => auth()->id(),
                'total' => round($total, 2),
                'down_payment' => round((float) $data['down_payment'], 2),
                'balance' => round(max($total - (float) $data['down_payment'], 0), 2),
                'status' => 'pending',
                'pickup_at' => $data['pickup_at'] ?? null,
            ]);

            foreach ($items as $entry) {
                $layaway->items()->create([
                    'product_id' => $entry['product']->id,
                    'quantity' => $entry['quantity'],
                    'line_total' => round($entry['line_total'], 2),
                ]);
            }

            if ((float) $data['down_payment'] > 0) {
                $layaway->payments()->create([
                    'amount' => round((float) $data['down_payment'], 2),
                    'payment_method' => 'efectivo',
                    'user_id' => auth()->id(),
                ]);
            }

            return $layaway->load(['customer', 'items.product']);
        });

        return response()->json(['message' => 'Apartado registrado.', 'layaway' => $layaway], 201);
    }

    public function pay(Request $request, Layaway $layaway): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'in:efectivo,tarjeta,mixto'],
        ]);

        if ($layaway->status !== 'pending') {
            throw ValidationException::withMessages(['layaway' => 'El apartado no esta pendiente.']);
        }

        $payment = DB::transaction(function () use ($layaway, $data) {
            $amount = min((float) $data['amount'], (float) $layaway->balance);

            $payment = $layaway->payments()->create([
                'amount' => round($amount, 2),
                'payment_method' => $data['payment_method'],
                'user_id' => auth()->id(),
            ]);

            $layaway->update([
                'balance' => round((float) $layaway->balance - $amount, 2),
                'status' => (float) $layaway->balance - $amount <= 0 ? 'completed' : 'pending',
            ]);

            return $payment;
        });

        return response()->json(['message' => 'Abono registrado.', 'payment' => $payment, 'layaway' => $layaway->fresh('payments')]);
    }

    public function complete(Layaway $layaway): JsonResponse
    {
        if ((float) $layaway->balance > 0) {
            throw ValidationException::withMessages(['layaway' => 'El apartado tiene saldo pendiente.']);
        }

        $layaway->update(['status' => 'completed']);

        return response()->json(['message' => 'Apartado completado.', 'layaway' => $layaway->fresh('items.product')]);
    }
}