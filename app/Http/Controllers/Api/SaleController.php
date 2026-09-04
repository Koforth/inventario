<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Movement;
use App\Models\Product;
use App\Models\ReceiptType;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $from = (string) $request->query('from', now()->toDateString());
        $to = (string) $request->query('to', now()->toDateString());

        $sales = Sale::query()
            ->with(['customer', 'user', 'items.product'])
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json($sales);
    }

    public function store(Request $request): JsonResponse
    {
        if (is_string($request->input('items'))) {
            $request->merge(['items' => json_decode((string) $request->input('items'), true) ?? []]);
        }

        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'sale_type' => ['required', 'in:contado,credito'],
            'payment_method' => ['required', 'in:efectivo,tarjeta,mixto'],
            'receipt_type_id' => ['required', 'exists:receipt_types,id'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'card_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.price_id' => ['nullable', 'exists:product_prices,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price_type' => ['nullable', 'integer', 'min:1'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $sale = DB::transaction(function () use ($data) {
            $cashRegister = CashRegister::where('status', 'open')->lockForUpdate()->latest('opened_at')->first();
            if (! $cashRegister) {
                throw ValidationException::withMessages(['cash_register' => 'Debes abrir caja antes de procesar ventas.']);
            }

            $products = Product::whereIn('id', collect($data['items'])->pluck('product_id')->all())
                ->with('prices')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lineItems = [];
            $subtotal = 0.0;
            $discountTotal = 0.0;

            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (int) $item['quantity'];
                $priceId = isset($item['price_id']) ? (int) $item['price_id'] : null;
                $priceType = (int) ($item['price_type'] ?? 1);
                $discount = min((float) ($item['discount'] ?? 0), 99999999.99);

                [$unitPrice, $resolvedType, $resolvedPriceId] = $this->resolvePrice($product, $priceId, $priceType);

                if ($product->inventoryable && $product->stock < $quantity) {
                    throw ValidationException::withMessages(['items' => "No hay stock suficiente para {$product->nombre}."]);
                }

                $lineSubtotal = $unitPrice * $quantity;
                $lineDiscount = min($discount, $lineSubtotal);

                $subtotal += $lineSubtotal;
                $discountTotal += $lineDiscount;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'price_id' => $resolvedPriceId,
                    'price_type' => $resolvedType,
                    'unit_price' => $unitPrice,
                    'discount' => $lineDiscount,
                    'line_subtotal' => $lineSubtotal,
                    'line_total' => $lineSubtotal - $lineDiscount,
                ];
            }

            $total = max($subtotal - $discountTotal, 0);
            $cashAmount = (float) ($data['cash_amount'] ?? 0);
            $cardAmount = (float) ($data['card_amount'] ?? 0);
            $paidAmount = $cashAmount + $cardAmount;
            $changeAmount = 0.0;
            $creditBalance = 0.0;

            if ($data['sale_type'] === 'contado') {
                if ($paidAmount < $total) {
                    throw ValidationException::withMessages(['payment' => 'El monto pagado no cubre el total de la venta.']);
                }
                if ($data['payment_method'] !== 'mixto' && $paidAmount > $total) {
                    $changeAmount = $data['payment_method'] === 'efectivo' ? $paidAmount - $total : 0;
                }
            } else {
                $customer = Customer::find((int) $data['customer_id']);
                if (! $customer || ! $customer->is_active) {
                    throw ValidationException::withMessages(['customer_id' => 'El cliente seleccionado no esta vigente.']);
                }
                $creditBalance = max($total - $paidAmount, 0);
                if ($creditBalance > (float) $customer->credit_limit) {
                    throw ValidationException::withMessages(['customer_id' => 'El saldo al credito supera el limite crediticio.']);
                }
            }

            $sale = Sale::create([
                'number' => 'VTA-' . now()->format('Ymd-His') . '-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT),
                'receipt_number' => null,
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'sale_type' => $data['sale_type'],
                'payment_method' => $data['payment_method'],
                'receipt_type' => 'ticket',
                'receipt_type_id' => $data['receipt_type_id'],
                'subtotal' => round($subtotal, 2),
                'discount_total' => round($discountTotal, 2),
                'total' => round($total, 2),
                'paid_amount' => round($paidAmount, 2),
                'cash_amount' => round($cashAmount, 2),
                'card_amount' => round($cardAmount, 2),
                'change_amount' => round($changeAmount, 2),
                'credit_balance' => round($creditBalance, 2),
            ]);

            $receiptType = ReceiptType::whereKey($data['receipt_type_id'])->lockForUpdate()->first();
            if (! $receiptType || ! $receiptType->is_active) {
                throw ValidationException::withMessages(['receipt_type_id' => 'Tipo de comprobante no valido.']);
            }

            $sale->update([
                'receipt_type' => in_array(strtolower($receiptType->code), ['ticket', 'factura'], true) ? strtolower($receiptType->code) : 'ticket',
                'receipt_number' => $receiptType->nextCorrelative(),
            ]);
            $receiptType->increment('current_number');

            foreach ($lineItems as $lineItem) {
                $product = $lineItem['product'];
                if ($product->inventoryable) {
                    $product->decrement('stock', $lineItem['quantity']);
                }

                $movement = Movement::create([
                    'product_id' => $product->id,
                    'tipo' => 'venta',
                    'cantidad' => $lineItem['quantity'],
                    'user_id' => auth()->id(),
                ]);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'product_price_id' => $lineItem['price_id'],
                    'price_type' => $lineItem['price_type'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => round($lineItem['unit_price'], 2),
                    'discount' => round($lineItem['discount'], 2),
                    'line_subtotal' => round($lineItem['line_subtotal'], 2),
                    'line_total' => round($lineItem['line_total'], 2),
                ]);

                $cashRegister->movements()->create([
                    'type' => 'sale',
                    'amount' => round($lineItem['line_total'], 2),
                    'description' => "Venta {$sale->receipt_number} - {$product->nombre}",
                    'reference_type' => Movement::class,
                    'reference_id' => $movement->id,
                    'user_id' => auth()->id(),
                ]);
            }

            return $sale->load(['customer', 'items.product', 'user']);
        });

        return response()->json(['message' => 'Venta procesada correctamente.', 'sale' => $sale], 201);
    }

    public function show(Sale $sale): JsonResponse
    {
        return response()->json($sale->load(['customer', 'user', 'items.product', 'payments']));
    }

    private function resolvePrice(Product $product, ?int $priceId, int $priceType): array
    {
        if ($priceId) {
            $price = $product->prices->where('is_active', true)->firstWhere('id', $priceId);
            if (! $price) {
                throw ValidationException::withMessages(['items' => "El precio seleccionado no pertenece al producto {$product->nombre}."]);
            }
            $activePrices = $product->prices->where('is_active', true)->values();
            $resolvedType = $activePrices->search(fn ($item) => $item->id === $price->id);

            return [(float) $price->amount, $resolvedType === false ? $priceType : $resolvedType + 1, $price->id];
        }

        $amount = match ($priceType) {
            2 => (float) ($product->sale_price_2 ?: $product->sale_price_1),
            3 => (float) ($product->sale_price_3 ?: $product->sale_price_1),
            default => (float) $product->sale_price_1,
        };

        return [max($amount, 0), $priceType, null];
    }

    public function payments(Request $request, Sale $sale): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:efectivo,tarjeta,mixto'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json([
            'message' => 'Abono registrado.',
            'sale' => $sale->fresh('payments'),
        ]);
    }
}