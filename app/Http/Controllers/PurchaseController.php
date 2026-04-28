<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    private const TAX_RATE = 0.15;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $month = $filters['month'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));

        $query = Purchase::query()
            ->with(['supplier', 'user'])
            ->when($month, fn ($query) => $query->whereYear('created_at', substr($month, 0, 4))->whereMonth('created_at', substr($month, 5, 2)))
            ->when(! $month && $dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when(! $month && $dateTo, fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->when($search !== '', function ($query) use ($search) {
                $query->where('number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            })
            ->latest();

        return view('purchases.index', [
            'purchases' => $query->paginate(12)->withQueryString(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'month' => $month,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::orderBy('nombre')->get(),
            'taxRate' => self::TAX_RATE,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'payment_type' => ['required', 'in:contado,credito'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $purchase = DB::transaction(function () use ($data) {
            $purchase = Purchase::create([
                'number' => $this->nextNumber(),
                'supplier_id' => $data['supplier_id'],
                'payment_type' => $data['payment_type'],
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            $subtotal = 0;
            $tax = 0;

            foreach ($data['items'] as $item) {
                $product = Product::whereKey($item['product_id'])->lockForUpdate()->firstOrFail();
                $quantity = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $lineSubtotal = $quantity * $unitCost;
                $lineTax = $product->taxable ? $lineSubtotal * self::TAX_RATE : 0;

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->nombre,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'previous_cost' => (float) $product->purchase_price,
                    'line_subtotal' => $lineSubtotal,
                    'line_tax' => $lineTax,
                    'line_total' => $lineSubtotal + $lineTax,
                ]);

                if ($product->inventoryable) {
                    $product->increment('stock', $quantity);
                }

                $product->update(['purchase_price' => $unitCost]);
                $subtotal += $lineSubtotal;
                $tax += $lineTax;
            }

            $total = $subtotal + $tax;
            $paid = $data['payment_type'] === 'contado' ? $total : min((float) ($data['paid_amount'] ?? 0), $total);
            $balance = $total - $paid;

            $purchase->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'paid_amount' => $paid,
                'balance' => $balance,
                'status' => $balance > 0 ? 'pendiente' : 'pagada',
            ]);

            if ($paid > 0 && $data['payment_type'] === 'credito') {
                $purchase->payments()->create([
                    'amount' => $paid,
                    'payment_date' => today(),
                    'notes' => 'Abono inicial',
                    'user_id' => auth()->id(),
                ]);
            }

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Compra registrada correctamente.');
    }

    public function show(Purchase $purchase): View
    {
        return view('purchases.show', [
            'purchase' => $purchase->load(['supplier', 'items.product', 'payments', 'user']),
        ]);
    }

    public function credits(): View
    {
        return view('purchases.credits', [
            'purchases' => Purchase::with('supplier')
                ->where('payment_type', 'credito')
                ->where('balance', '>', 0)
                ->latest()
                ->paginate(12),
        ]);
    }

    public function payment(Request $request, Purchase $purchase): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($purchase, $data) {
            $amount = min((float) $data['amount'], (float) $purchase->balance);

            $purchase->payments()->create([
                'amount' => $amount,
                'payment_date' => $data['payment_date'],
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            $paid = (float) $purchase->paid_amount + $amount;
            $balance = max((float) $purchase->total - $paid, 0);

            $purchase->update([
                'paid_amount' => $paid,
                'balance' => $balance,
                'status' => $balance > 0 ? 'pendiente' : 'pagada',
            ]);
        });

        return back()->with('success', 'Abono registrado correctamente.');
    }

    public function priceHistory(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('purchases.price-history', [
            'items' => \App\Models\PurchaseItem::query()
                ->with(['purchase.supplier', 'product'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where('product_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    private function nextNumber(): string
    {
        $prefix = 'COM-' . now()->format('Ymd') . '-';
        $next = Purchase::where('number', 'like', $prefix . '%')->count() + 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
