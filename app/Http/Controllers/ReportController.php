<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'tipo' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $movementTypes = Movement::query()
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo')
            ->filter()
            ->values();

        $tipo = (string) ($filters['tipo'] ?? 'todos');
        $tipo = $tipo === '' ? 'todos' : $tipo;
        if ($tipo !== 'todos' && ! $movementTypes->contains($tipo)) {
            $tipo = 'todos';
        }
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));

        $movementQuery = $this->movementQuery($tipo, $dateFrom, $dateTo, $search);

        $summary = (clone $movementQuery)
            ->toBase()
            ->selectRaw('COUNT(*) as total_movements')
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN cantidad ELSE 0 END), 0) as total_entries")
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'venta' THEN cantidad ELSE 0 END), 0) as total_sales")
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'merma' THEN cantidad ELSE 0 END), 0) as total_losses")
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'traslado' THEN cantidad ELSE 0 END), 0) as total_transfers")
            ->first();

        $salesValue = (clone $movementQuery)
            ->where('tipo', 'venta')
            ->join('products', 'movements.product_id', '=', 'products.id')
            ->selectRaw('COALESCE(SUM(movements.cantidad * products.precio), 0) as total')
            ->value('total');

        $movements = $movementQuery
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $lowStockProducts = Product::query()
            ->with(['brand', 'category'])
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->orderBy('nombre')
            ->limit(8)
            ->get();

        $topSellingProducts = Movement::query()
            ->select('product_id', DB::raw('SUM(cantidad) as total_quantity'))
            ->with('product')
            ->where('tipo', 'venta')
            ->when($dateFrom, fn ($query) => $query->whereDate('movements.created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('movements.created_at', '<=', $dateTo))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        $stats = [
            'total_movements' => (int) $summary->total_movements,
            'total_entries' => (int) $summary->total_entries,
            'total_sales' => (int) $summary->total_sales,
            'total_losses' => (int) $summary->total_losses,
            'total_transfers' => (int) $summary->total_transfers,
            'sales_value' => (float) $salesValue,
            'low_stock' => Product::whereColumn('stock', '<=', 'stock_minimo')->count(),
            'inventory_value' => (float) Product::selectRaw('COALESCE(SUM(stock * precio), 0) as total')->value('total'),
        ];

        return view('reports.index', compact(
            'movements',
            'lowStockProducts',
            'topSellingProducts',
            'stats',
            'tipo',
            'dateFrom',
            'dateTo',
            'search',
            'movementTypes',
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'tipo' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $movementTypes = Movement::query()
            ->distinct()
            ->pluck('tipo')
            ->filter()
            ->values();

        $tipo = (string) ($filters['tipo'] ?? 'todos');
        $tipo = $tipo === '' ? 'todos' : $tipo;
        if ($tipo !== 'todos' && ! $movementTypes->contains($tipo)) {
            $tipo = 'todos';
        }
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));
        $fileName = 'reporte-inventario-' . now()->format('Y-m-d-His') . '.xls';

        return response()->streamDownload(function () use ($tipo, $dateFrom, $dateTo, $search) {
            echo view('reports.export', [
                'movements' => $this->movementQuery($tipo, $dateFrom, $dateTo, $search)
                    ->oldest('movements.created_at')
                    ->get(),
                'tipo' => $tipo,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'search' => $search,
                'generatedAt' => now(),
            ])->render();
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    private function movementQuery(string $tipo, ?string $dateFrom, ?string $dateTo, string $search): Builder
    {
        return Movement::query()
            ->with(['product.brand', 'product.category', 'user'])
            ->when($tipo !== 'todos', fn ($query) => $query->where('tipo', $tipo))
            ->when($dateFrom, fn ($query) => $query->whereDate('movements.created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('movements.created_at', '<=', $dateTo))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->whereHas('product', function ($query) use ($search) {
                            $query
                                ->where('nombre', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            });
    }

    public function salesReport(Request $request): View
    {
        $params = $this->parseRange($request);

        $salesQuery = Sale::query()->with(['customer', 'user', 'items.product', 'receiptType']);

        $salesQuery = $this->applyRange($salesQuery, 'sales.created_at', $params['from'], $params['to'])
            ->when($request->query('sale_type') && $request->query('sale_type') !== 'all',
                fn ($q) => $q->where('sale_type', $request->query('sale_type')));

        $sales = (clone $salesQuery)->latest()->paginate(15)->withQueryString();

        $totals = (clone $salesQuery)->toBase()->selectRaw('
            COUNT(*) as count,
            COALESCE(SUM(total), 0) as total,
            COALESCE(SUM(discount_total), 0) as discounts,
            COALESCE(SUM(credit_balance), 0) as credit_pending
        ')->first();

        return view('reports.sales', [
            'sales' => $sales,
            'totals' => $totals,
            'saleType' => in_array((string) $request->query('sale_type', 'all'), ['contado', 'credito'], true) ? $request->query('sale_type') : 'all',
            'from' => $params['from']->format('Y-m-d'),
            'to' => $params['to']->format('Y-m-d'),
        ]);
    }

    public function exportSales(Request $request): StreamedResponse
    {
        $params = $this->parseRange($request);

        $sales = Sale::query()
            ->with(['customer', 'user', 'items.product'])
            ->whereBetween('created_at', [$params['from'], $params['to']])
            ->oldest()
            ->get();

        return response()->streamDownload(function () use ($sales, $params) {
            echo view('reports.export-sales', [
                'sales' => $sales,
                'from' => $params['from'],
                'to' => $params['to'],
                'generatedAt' => now(),
            ])->render();
        }, 'reporte-ventas-' . now()->format('Y-m-d-His') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function purchasesReport(Request $request): View
    {
        $params = $this->parseRange($request);

        $purchasesQuery = Purchase::query()->with(['supplier', 'user', 'items.product']);

        $purchasesQuery = $this->applyRange($purchasesQuery, 'purchases.created_at', $params['from'], $params['to'])
            ->when($request->query('supplier_id') && $request->query('supplier_id') !== 'all',
                fn ($q) => $q->where('supplier_id', $request->query('supplier_id')));

        $purchases = (clone $purchasesQuery)->latest()->paginate(15)->withQueryString();

        $totals = (clone $purchasesQuery)->toBase()->selectRaw('
            COUNT(*) as count,
            COALESCE(SUM(total), 0) as total
        ')->first();

        return view('reports.purchases', [
            'purchases' => $purchases,
            'totals' => $totals,
            'suppliers' => Supplier::orderBy('name')->get(),
            'supplierId' => (string) $request->query('supplier_id', 'all'),
            'from' => $params['from']->format('Y-m-d'),
            'to' => $params['to']->format('Y-m-d'),
        ]);
    }

    public function exportPurchases(Request $request): StreamedResponse
    {
        $params = $this->parseRange($request);

        $purchases = Purchase::query()
            ->with(['supplier', 'user'])
            ->whereBetween('created_at', [$params['from'], $params['to']])
            ->oldest()
            ->get();

        return response()->streamDownload(function () use ($purchases, $params) {
            echo view('reports.export-purchases', [
                'purchases' => $purchases,
                'from' => $params['from'],
                'to' => $params['to'],
                'generatedAt' => now(),
            ])->render();
        }, 'reporte-compras-' . now()->format('Y-m-d-His') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function debtorsReport(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $debtors = Customer::query()
            ->where('is_active', true)
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%")
                    ->orWhere('ruc', 'like', "%{$search}%");
            }))
            ->get()
            ->map(function ($customer) {
                $balance = (float) Sale::where('customer_id', $customer->id)
                    ->where('sale_type', 'credito')
                    ->sum('credit_balance');

                return ['customer' => $customer, 'balance' => $balance];
            })
            ->filter(fn ($item) => $item['balance'] > 0)
            ->sortByDesc('balance')
            ->values();

        return view('reports.debtors', [
            'debtors' => $debtors,
            'totalPending' => round((float) $debtors->sum('balance'), 2),
            'search' => $search,
        ]);
    }

    public function exportDebtors(): StreamedResponse
    {
        $debtors = Customer::query()
            ->where('is_active', true)
            ->get()
            ->map(function ($customer) {
                $balance = (float) Sale::where('customer_id', $customer->id)
                    ->where('sale_type', 'credito')
                    ->sum('credit_balance');

                return ['customer' => $customer, 'balance' => $balance];
            })
            ->filter(fn ($item) => $item['balance'] > 0)
            ->sortByDesc('balance')
            ->values();

        return response()->streamDownload(function () use ($debtors) {
            echo view('reports.export-debtors', [
                'debtors' => $debtors,
                'generatedAt' => now(),
            ])->render();
        }, 'reporte-morosos-' . now()->format('Y-m-d-His') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function catalogReport(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::query()
            ->with(['category', 'brand', 'presentation', 'suppliers', 'prices'])
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            }))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('reports.catalog', [
            'products' => $products,
            'search' => $search,
            'stats' => [
                'total' => Product::count(),
                'inventory_value' => (float) Product::selectRaw('COALESCE(SUM(stock * purchase_price), 0) as total')->value('total'),
                'categories' => \App\Models\Category::count(),
            ],
        ]);
    }

    public function exportCatalog(): StreamedResponse
    {
        $products = Product::query()
            ->with(['category', 'brand', 'presentation', 'suppliers'])
            ->orderBy('nombre')
            ->get();

        return response()->streamDownload(function () use ($products) {
            echo view('reports.export-catalog', [
                'products' => $products,
                'generatedAt' => now(),
            ])->render();
        }, 'reporte-catalogo-' . now()->format('Y-m-d-His') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    private function parseRange(Request $request): array
    {
        $from = \Illuminate\Support\Carbon::parse((string) $request->query('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = \Illuminate\Support\Carbon::parse((string) $request->query('to', now()->toDateString()))->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return ['from' => $from, 'to' => $to];
    }

    private function applyRange(Builder $query, string $column, \Carbon\CarbonInterface $from, \Carbon\CarbonInterface $to): Builder
    {
        return $query->whereBetween($column, [$from, $to]);
    }
}
