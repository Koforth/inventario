<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuoteController extends Controller
{
    private const TAX_RATE = 0.15;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $dateFrom = $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? now()->format('Y-m-d');
        $search = trim((string) ($filters['search'] ?? ''));

        $quotes = Quote::query()
            ->with('user')
            ->when($dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('quotes.index', compact('quotes', 'dateFrom', 'dateTo', 'search'));
    }

    public function create(): View
    {
        return view('quotes.create', [
            'products' => Product::query()
                ->with(['brand', 'presentation'])
                ->orderBy('nombre')
                ->get(),
            'taxRate' => self::TAX_RATE,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $quote = DB::transaction(function () use ($data) {
            $quote = Quote::create([
                'number' => $this->nextNumber(),
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            $subtotal = 0;
            $tax = 0;
            $total = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $product->sale_price_1;
                $lineSubtotal = $quantity * $unitPrice;
                $lineTax = $product->taxable ? $lineSubtotal * self::TAX_RATE : 0;
                $lineTotal = $lineSubtotal + $lineTax;

                $quote->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->nombre,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'taxable' => $product->taxable,
                    'line_subtotal' => $lineSubtotal,
                    'line_tax' => $lineTax,
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $lineSubtotal;
                $tax += $lineTax;
                $total += $lineTotal;
            }

            $quote->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            return $quote;
        });

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'Cotizacion generada correctamente.');
    }

    public function show(Quote $quote): View
    {
        return view('quotes.show', [
            'quote' => $quote->load(['items.product', 'user']),
        ]);
    }

    private function nextNumber(): string
    {
        $prefix = 'COT-' . now()->format('Ymd') . '-';
        $next = Quote::where('number', 'like', $prefix . '%')->count() + 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
