<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Movement;
use App\Models\Product;
use App\Models\ReceiptType;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $reportType = (string) $request->query('report_type', 'day');
        $from = (string) $request->query('from', now()->toDateString());
        $to = (string) $request->query('to', now()->toDateString());
        $month = (string) $request->query('month', now()->format('Y-m'));

        $salesQuery = Sale::query()->with(['customer', 'user', 'items.product']);

        if ($reportType === 'range') {
            $fromDate = Carbon::parse($from)->startOfDay();
            $toDate = Carbon::parse($to)->endOfDay();
            if ($fromDate->gt($toDate)) {
                [$fromDate, $toDate] = [$toDate, $fromDate];
            }
            $salesQuery->whereBetween('created_at', [$fromDate, $toDate]);
        } elseif ($reportType === 'month') {
            $monthDate = Carbon::createFromFormat('Y-m', preg_match('/^\d{4}-\d{2}$/', $month) ? $month : now()->format('Y-m'));
            $salesQuery
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month);
        } else {
            $salesQuery->whereDate('created_at', today());
            $reportType = 'day';
        }

        $filteredSales = $salesQuery->latest()->paginate(10, ['*'], 'sales_page')->withQueryString();

        $salesTotals = [
            'count' => $filteredSales->total(),
            'amount' => (float) (clone $salesQuery)->sum('total'),
        ];

        return view('inventory.sales', [
            'products' => $this->productOptions($request, onlyAvailable: true),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'receiptTypes' => ReceiptType::where('is_active', true)->orderBy('name')->get(),
            'recentSales' => Sale::with(['customer', 'user'])->latest()->limit(6)->get(),
            'search' => trim((string) $request->query('search', '')),
            'filteredSales' => $filteredSales,
            'salesTotals' => $salesTotals,
            'reportType' => $reportType,
            'from' => $from,
            'to' => $to,
            'month' => $month,
            'stats' => [
                'today_sales' => Sale::whereDate('created_at', today())->count(),
                'today_amount' => (float) Sale::whereDate('created_at', today())->sum('total'),
                'available_products' => Product::where('stock', '>', 0)->count(),
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
        if (is_string($request->input('items'))) {
            $request->merge([
                'items' => json_decode((string) $request->input('items'), true) ?? [],
            ]);
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
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price_type' => ['required', 'integer', 'in:1,2,3'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $sale = DB::transaction(function () use ($data) {
            $cashRegister = CashRegister::where('status', 'open')->lockForUpdate()->latest('opened_at')->first();
            if (! $cashRegister) {
                throw ValidationException::withMessages([
                    'cash_register' => 'Debes abrir caja antes de procesar ventas.',
                ]);
            }

            $products = Product::whereIn('id', collect($data['items'])->pluck('product_id')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lineItems = [];
            $subtotal = 0.0;
            $discountTotal = 0.0;

            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (int) $item['quantity'];
                $priceType = (int) $item['price_type'];
                $discount = min((float) ($item['discount'] ?? 0), 99999999.99);
                $unitPrice = $this->resolvePriceByType($product, $priceType);

                if ($product->inventoryable && $product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "No hay stock suficiente para {$product->nombre}.",
                    ]);
                }

                $lineSubtotal = $unitPrice * $quantity;
                $lineDiscount = min($discount, $lineSubtotal);
                $lineTotal = $lineSubtotal - $lineDiscount;

                $subtotal += $lineSubtotal;
                $discountTotal += $lineDiscount;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'price_type' => $priceType,
                    'unit_price' => $unitPrice,
                    'discount' => $lineDiscount,
                    'line_subtotal' => $lineSubtotal,
                    'line_total' => $lineTotal,
                ];
            }

            $total = max($subtotal - $discountTotal, 0);
            $cashAmount = (float) ($data['cash_amount'] ?? 0);
            $cardAmount = (float) ($data['card_amount'] ?? 0);
            [$cashAmount, $cardAmount] = $this->normalizePayment($data['payment_method'], $cashAmount, $cardAmount);
            $paidAmount = $cashAmount + $cardAmount;
            $changeAmount = 0.0;
            $creditBalance = 0.0;

            if ($data['sale_type'] === 'contado') {
                if ($paidAmount < $total) {
                    throw ValidationException::withMessages([
                        'payment' => 'El monto pagado no cubre el total de la venta.',
                    ]);
                }

                if ($data['payment_method'] !== 'mixto' && $paidAmount > $total) {
                    $changeAmount = $data['payment_method'] === 'efectivo' ? $paidAmount - $total : 0;
                }
            } else {
                if ((int) $data['customer_id'] <= 0) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'Debes seleccionar un cliente para ventas al credito.',
                    ]);
                }

                $customer = Customer::find($data['customer_id']);
                if (! $customer || ! $customer->is_active) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'El cliente seleccionado no esta vigente.',
                    ]);
                }

                $creditBalance = max($total - $paidAmount, 0);
                if ($creditBalance > (float) $customer->credit_limit) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'El saldo al credito supera el limite crediticio del cliente.',
                    ]);
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
                throw ValidationException::withMessages([
                    'receipt_type_id' => 'Tipo de comprobante no valido o inactivo.',
                ]);
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

            return $sale->load(['customer', 'items.product']);
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Venta procesada correctamente.', 'sale' => $sale], 201);
        }

        return redirect()
            ->route('inventory.sales.index')
            ->with('success', "Venta {$sale->number} procesada correctamente.");
    }

    private function normalizePayment(string $method, float $cashAmount, float $cardAmount): array
    {
        if ($method === 'efectivo') {
            return [$cashAmount, 0.0];
        }

        if ($method === 'tarjeta') {
            return [0.0, $cardAmount];
        }

        return [$cashAmount, $cardAmount];
    }

    private function resolvePriceByType(Product $product, int $priceType): float
    {
        $price = match ($priceType) {
            2 => (float) ($product->sale_price_2 ?: $product->sale_price_1),
            3 => (float) ($product->sale_price_3 ?: $product->sale_price_1),
            default => (float) $product->sale_price_1,
        };

        return max($price, 0);
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
