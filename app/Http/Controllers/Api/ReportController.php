<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function movements(Request $request): JsonResponse
    {
        $movements = Movement::query()
            ->with(['product.brand', 'product.category', 'user'])
            ->when($request->query('tipo'), fn ($q, $tipo) => $q->where('tipo', $tipo))
            ->when($request->query('from'), fn ($q, $from) => $q->whereDate('movements.created_at', '>=', $from))
            ->when($request->query('to'), fn ($q, $to) => $q->whereDate('movements.created_at', '<=', $to))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json($movements);
    }

    public function sales(Request $request): JsonResponse
    {
        $sales = Sale::query()
            ->with(['customer', 'user', 'items.product'])
            ->when($request->query('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->when($request->query('sale_type'), fn ($q, $type) => $q->where('sale_type', $type))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json([
            'totals' => [
                'count' => $sales->total(),
                'amount' => (float) (clone $sales->toBase())->sum('total'),
            ],
            'sales' => $sales,
        ]);
    }

    public function purchases(Request $request): JsonResponse
    {
        $purchases = Purchase::query()
            ->with(['supplier', 'user'])
            ->when($request->query('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json($purchases);
    }

    public function debtors(): JsonResponse
    {
        $debtors = Customer::query()
            ->where('is_active', true)
            ->withCount('sales')
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'dni' => $customer->dni,
                    'ruc' => $customer->ruc,
                    'credit_limit' => (float) $customer->credit_limit,
                    'pending_balance' => (float) Sale::where('customer_id', $customer->id)
                        ->where('sale_type', 'credito')
                        ->sum('credit_balance'),
                    'sales_count' => (int) $customer->sales_count,
                ];
            })
            ->filter(fn ($item) => $item['pending_balance'] > 0)
            ->sortByDesc('pending_balance')
            ->values();

        return response()->json([
            'total_pending' => (float) $debtors->sum('pending_balance'),
            'debtors' => $debtors,
        ]);
    }

    public function catalog(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'brand', 'presentation', 'prices'])
            ->when($request->query('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json($products);
    }

    public function lowStock(): JsonResponse
    {
        $products = Product::query()
            ->with(['brand', 'category'])
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->get();

        return response()->json([
            'count' => $products->count(),
            'products' => $products,
        ]);
    }

    public function topSelling(Request $request): JsonResponse
    {
        $top = Movement::query()
            ->with('product')
            ->where('tipo', 'venta')
            ->when($request->query('from'), fn ($q, $from) => $q->whereDate('movements.created_at', '>=', $from))
            ->when($request->query('to'), fn ($q, $to) => $q->whereDate('movements.created_at', '<=', $to))
            ->select('product_id', DB::raw('SUM(cantidad) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit($request->integer('limit', 5))
            ->get()
            ->map(fn ($item) => [
                'product' => $item->product,
                'total_quantity' => (int) $item->total_quantity,
            ]);

        return response()->json($top);
    }
}