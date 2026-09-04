<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Layaway;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\WorkshopOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $openCash = CashRegister::with('movements')->where('status', 'open')->latest('opened_at')->first();

        $salesLast7Days = Sale::where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartDates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $chartDates->push(Carbon::now()->subDays($i)->toDateString());
        }

        return response()->json([
            'stats' => [
                'today_sales_count' => (int) Sale::whereDate('created_at', today())->count(),
                'today_sales_amount' => (float) Sale::whereDate('created_at', today())->sum('total'),
                'low_stock' => Product::whereColumn('stock', '<=', 'stock_minimo')->count(),
                'inventory_value' => (float) Product::selectRaw('COALESCE(SUM(stock * purchase_price), 0) as total')->value('total'),
                'credit_sales_pending' => (float) Sale::where('sale_type', 'credito')->sum('credit_balance'),
                'layaways_pending' => Layaway::where('status', 'pending')->count(),
                'cash_open' => (bool) $openCash,
                'cash_in_today' => (float) ($openCash?->movements->whereIn('type', ['sale', 'income'])->sum('amount') ?? 0),
                'workshop_open' => WorkshopOrder::whereIn('status', ['open', 'in_progress'])->count(),
                'purchases_month' => (float) Purchase::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('total'),
            ],
            'charts' => [
                'sales_last_7_days' => $chartDates->map(function ($date) use ($salesLast7Days) {
                    return [
                        'date' => $date,
                        'count' => (int) ($salesLast7Days->get($date)?->count ?? 0),
                        'amount' => (float) ($salesLast7Days->get($date)?->amount ?? 0),
                    ];
                }),
                'products_by_category' => Product::query()
                    ->with('category')
                    ->selectRaw('category_id, COUNT(*) as total')
                    ->whereNotNull('category_id')
                    ->groupBy('category_id')
                    ->orderByDesc('total')
                    ->limit(6)
                    ->get()
                    ->map(fn ($item) => ['label' => $item->category?->nombre ?? 'Sin categoria', 'value' => (int) $item->total]),
                'top_selling' => Movement::query()
                    ->with('product')
                    ->where('tipo', 'venta')
                    ->select('product_id', DB::raw('SUM(cantidad) as total_quantity'))
                    ->groupBy('product_id')
                    ->orderByDesc('total_quantity')
                    ->limit(5)
                    ->get()
                    ->map(fn ($item) => ['label' => $item->product?->nombre ?? 'Producto eliminado', 'value' => (int) $item->total_quantity]),
            ],
            'recent_sales' => Sale::with('customer')->latest()->limit(5)->get(),
        ]);
    }
}