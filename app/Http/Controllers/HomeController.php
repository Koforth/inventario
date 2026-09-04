<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Layaway;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\WorkshopOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        if (auth()->user()->hasRole('invitado')) {
            return view('home-invite');
        }

        $todaySalesQuery = Sale::whereDate('created_at', today());

        $openCash = CashRegister::with('movements')
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        $cashInToday = $openCash
            ? (float) $openCash->movements->whereIn('type', ['sale', 'income'])->sum('amount')
            : 0.0;

        $salesLast7Days = Sale::query()
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartDates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $chartDates->push(Carbon::now()->subDays($i)->toDateString());
        }

        $salesSeries = $chartDates->map(fn ($date) => (float) ($salesLast7Days->get($date)?->amount ?? 0));
        $salesCountSeries = $chartDates->map(fn ($date) => (int) ($salesLast7Days->get($date)?->count ?? 0));

        $salesByCategory = Product::query()
            ->with('category')
            ->selectRaw('category_id, COUNT(*) as total')
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($item) => [
                'label' => $item->category?->nombre ?? 'Sin categoria',
                'value' => (int) $item->total,
            ]);

        $topSellingProducts = Movement::query()
            ->with('product')
            ->where('tipo', 'venta')
            ->select('product_id', DB::raw('SUM(cantidad) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'label' => $item->product?->nombre ?? 'Producto eliminado',
                'value' => (int) $item->total_quantity,
            ]);

        return view('home', [
            'stats' => [
                'today_sales_count' => (int) $todaySalesQuery->count(),
                'today_sales_amount' => (float) (clone $todaySalesQuery)->sum('total'),
                'low_stock' => Product::whereColumn('stock', '<=', 'stock_minimo')->count(),
                'inventory_value' => (float) Product::selectRaw('COALESCE(SUM(stock * purchase_price), 0) as total')->value('total'),
                'credit_sales_pending' => (float) Sale::where('sale_type', 'credito')->sum('credit_balance'),
                'layaways_pending' => Layaway::where('status', 'pending')->count(),
                'cash_open' => (bool) $openCash,
                'cash_in_today' => $cashInToday,
                'workshop_open' => WorkshopOrder::whereIn('status', ['open', 'in_progress'])->count(),
                'purchases_month' => (float) Purchase::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('total'),
            ],
            'recentSales' => Sale::with('customer')->latest()->limit(5)->get(),
            'recentMovements' => Movement::with('product')->latest()->limit(6)->get(),
            'nextLayaways' => Layaway::with('customer')
                ->where('status', 'pending')
                ->whereDate('pickup_at', '>=', today())
                ->orderBy('pickup_at')
                ->limit(5)
                ->get(),
            'recentWorkshopOrders' => WorkshopOrder::with(['customer', 'technician'])->latest()->limit(5)->get(),
            'chartLabels' => $chartDates->map(fn ($date) => Carbon::parse($date)->format('d/m'))->values(),
            'salesSeries' => $salesSeries->values(),
            'salesCountSeries' => $salesCountSeries->values(),
            'salesByCategory' => $salesByCategory,
            'topSellingProducts' => $topSellingProducts,
        ]);
    }
}
