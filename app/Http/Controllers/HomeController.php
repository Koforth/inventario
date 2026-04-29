<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Layaway;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\WorkshopOrder;
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
        ]);
    }
}
