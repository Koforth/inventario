<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'tipo' => ['nullable', 'in:todos,entrada,venta,merma,traslado'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $tipo = $filters['tipo'] ?? 'todos';
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));

        $movementQuery = Movement::query()
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
        ));
    }
}
