<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Layaway;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LayawayController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $reportType = (string) $request->query('report_type', 'day');
        $from = (string) $request->query('from', now()->toDateString());
        $to = (string) $request->query('to', now()->toDateString());
        $month = (string) $request->query('month', now()->format('Y-m'));

        $layawayQuery = Layaway::query()
            ->with(['customer', 'items.product', 'payments'])
            ->where('status', 'pending');

        if ($reportType === 'range') {
            $fromDate = Carbon::parse($from)->startOfDay();
            $toDate = Carbon::parse($to)->endOfDay();
            if ($fromDate->gt($toDate)) {
                [$fromDate, $toDate] = [$toDate, $fromDate];
            }
            $layawayQuery->whereBetween('pickup_at', [$fromDate, $toDate]);
        } elseif ($reportType === 'month') {
            $monthDate = Carbon::createFromFormat('Y-m', preg_match('/^\d{4}-\d{2}$/', $month) ? $month : now()->format('Y-m'));
            $layawayQuery->whereYear('pickup_at', $monthDate->year)->whereMonth('pickup_at', $monthDate->month);
        } else {
            $layawayQuery->whereDate('pickup_at', today());
            $reportType = 'day';
        }

        $layaways = $layawayQuery->latest('pickup_at')->paginate(10)->withQueryString();
        $creditSales = Sale::query()
            ->with(['customer', 'items.product', 'payments'])
            ->where('sale_type', 'credito')
            ->where('credit_balance', '>', 0)
            ->latest()
            ->paginate(10, ['*'], 'credits_page')
            ->withQueryString();

        return view('inventory.layaways', [
            'products' => $this->productOptions($search),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'search' => $search,
            'layaways' => $layaways,
            'creditSales' => $creditSales,
            'reportType' => $reportType,
            'from' => $from,
            'to' => $to,
            'month' => $month,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (is_string($request->input('items'))) {
            $request->merge(['items' => json_decode((string) $request->input('items'), true) ?? []]);
        }

        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'pickup_at' => ['required', 'date', 'after_or_equal:today'],
            'initial_payment' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price_type' => ['required', 'integer', 'in:1,2,3'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $register = CashRegister::where('status', 'open')->lockForUpdate()->latest('opened_at')->first();
            $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0.0;
            $discountTotal = 0.0;
            $lineItems = [];

            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (int) $item['quantity'];
                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages(['items' => "Stock insuficiente para {$product->nombre}."]);
                }

                $unitPrice = $this->resolvePriceByType($product, (int) $item['price_type']);
                $lineSubtotal = $unitPrice * $quantity;
                $lineDiscount = min((float) ($item['discount'] ?? 0), $lineSubtotal);
                $lineTotal = $lineSubtotal - $lineDiscount;
                $subtotal += $lineSubtotal;
                $discountTotal += $lineDiscount;

                $lineItems[] = [
                    'product' => $product,
                    'price_type' => (int) $item['price_type'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $lineDiscount,
                    'line_subtotal' => $lineSubtotal,
                    'line_total' => $lineTotal,
                ];
            }

            $total = max($subtotal - $discountTotal, 0);
            $initialPayment = min((float) $data['initial_payment'], $total);
            $balance = $total - $initialPayment;

            $layaway = Layaway::create([
                'number' => 'APT-' . now()->format('Ymd-His') . '-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT),
                'customer_id' => $data['customer_id'],
                'user_id' => auth()->id(),
                'pickup_at' => $data['pickup_at'],
                'subtotal' => round($subtotal, 2),
                'discount_total' => round($discountTotal, 2),
                'total' => round($total, 2),
                'initial_payment' => round($initialPayment, 2),
                'balance' => round($balance, 2),
            ]);

            foreach ($lineItems as $lineItem) {
                $lineItem['product']->decrement('stock', $lineItem['quantity']);

                Movement::create([
                    'product_id' => $lineItem['product']->id,
                    'tipo' => 'traslado',
                    'cantidad' => $lineItem['quantity'],
                    'user_id' => auth()->id(),
                ]);

                $layaway->items()->create([
                    'product_id' => $lineItem['product']->id,
                    'price_type' => $lineItem['price_type'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => round($lineItem['unit_price'], 2),
                    'discount' => round($lineItem['discount'], 2),
                    'line_subtotal' => round($lineItem['line_subtotal'], 2),
                    'line_total' => round($lineItem['line_total'], 2),
                ]);
            }

            if ($initialPayment > 0) {
                if (! $register) {
                    throw ValidationException::withMessages(['initial_payment' => 'Debes abrir caja para registrar el abono inicial.']);
                }

                $layaway->payments()->create([
                    'user_id' => auth()->id(),
                    'amount' => $initialPayment,
                    'notes' => 'Abono inicial',
                ]);

                $register->movements()->create([
                    'type' => 'income',
                    'amount' => $initialPayment,
                    'description' => "Abono inicial apartado {$layaway->number}",
                    'user_id' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('inventory.layaways.index')->with('success', 'Apartado guardado correctamente.');
    }

    public function pay(Request $request, Layaway $layaway): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($layaway, $data) {
            $layaway = Layaway::whereKey($layaway->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($layaway->status !== 'pending') {
                throw ValidationException::withMessages(['amount' => 'Este apartado ya fue finalizado.']);
            }

            $register = CashRegister::where('status', 'open')->lockForUpdate()->latest('opened_at')->first();
            if (! $register) {
                throw ValidationException::withMessages(['amount' => 'Debes abrir caja para registrar abonos.']);
            }

            $amount = min((float) $data['amount'], (float) $layaway->balance);
            $layaway->payments()->create(['user_id' => auth()->id(), 'amount' => $amount, 'notes' => $data['notes'] ?? null]);
            $layaway->decrement('balance', $amount);
            $layaway->refresh();

            $register->movements()->create([
                'type' => 'income',
                'amount' => $amount,
                'description' => "Abono apartado {$layaway->number}",
                'user_id' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Abono del apartado registrado.');
    }

    public function complete(Request $request, Layaway $layaway): RedirectResponse
    {
        DB::transaction(function () use ($layaway) {
            $layaway = Layaway::query()
                ->with('items')
                ->whereKey($layaway->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($layaway->status !== 'pending') {
                throw ValidationException::withMessages(['complete' => 'El apartado ya fue finalizado.']);
            }

            $register = CashRegister::where('status', 'open')->lockForUpdate()->latest('opened_at')->first();
            if (! $register) {
                throw ValidationException::withMessages(['complete' => 'Debes abrir caja para finalizar apartados.']);
            }

            if ((float) $layaway->balance > 0) {
                throw ValidationException::withMessages(['complete' => 'El apartado aun tiene saldo pendiente.']);
            }

            $sale = Sale::create([
                'number' => 'VTA-' . now()->format('Ymd-His') . '-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT),
                'customer_id' => $layaway->customer_id,
                'user_id' => auth()->id(),
                'sale_type' => 'contado',
                'payment_method' => 'efectivo',
                'receipt_type' => 'ticket',
                'subtotal' => $layaway->subtotal,
                'discount_total' => $layaway->discount_total,
                'total' => $layaway->total,
                'paid_amount' => $layaway->total,
                'cash_amount' => $layaway->total,
                'card_amount' => 0,
                'change_amount' => 0,
                'credit_balance' => 0,
            ]);

            foreach ($layaway->items as $item) {
                $sale->items()->create([
                    'product_id' => $item->product_id,
                    'price_type' => $item->price_type,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'line_subtotal' => $item->line_subtotal,
                    'line_total' => $item->line_total,
                ]);

                Movement::create([
                    'product_id' => $item->product_id,
                    'tipo' => 'venta',
                    'cantidad' => $item->quantity,
                    'user_id' => auth()->id(),
                ]);
            }

            $layaway->update(['status' => 'completed']);
        });

        return back()->with('success', 'Apartado finalizado y venta generada.');
    }

    public function payCredit(Request $request, Sale $sale): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($sale, $data) {
            $sale = Sale::whereKey($sale->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sale->sale_type !== 'credito' || (float) $sale->credit_balance <= 0) {
                throw ValidationException::withMessages(['amount' => 'Esta venta no tiene saldo pendiente.']);
            }

            $register = CashRegister::where('status', 'open')->lockForUpdate()->latest('opened_at')->first();
            if (! $register) {
                throw ValidationException::withMessages(['amount' => 'Debes abrir caja para registrar abonos.']);
            }

            $amount = min((float) $data['amount'], (float) $sale->credit_balance);
            $sale->payments()->create(['user_id' => auth()->id(), 'amount' => $amount, 'notes' => $data['notes'] ?? null]);
            $sale->increment('paid_amount', $amount);
            $sale->decrement('credit_balance', $amount);

            $register->movements()->create([
                'type' => 'income',
                'amount' => $amount,
                'description' => "Abono venta credito {$sale->number}",
                'user_id' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Abono de venta al credito registrado.');
    }

    private function productOptions(string $search)
    {
        return Product::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->where('stock', '>', 0)
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();
    }

    private function resolvePriceByType(Product $product, int $priceType): float
    {
        return match ($priceType) {
            2 => (float) ($product->sale_price_2 ?: $product->sale_price_1),
            3 => (float) ($product->sale_price_3 ?: $product->sale_price_1),
            default => (float) $product->sale_price_1,
        };
    }
}
