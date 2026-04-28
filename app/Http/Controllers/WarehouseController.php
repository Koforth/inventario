<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Presentation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        return view('warehouse.index', [
            'stats' => [
                'categories' => Category::count(),
                'brands' => Brand::count(),
                'presentations' => Presentation::count(),
                'products' => Product::count(),
                'perishables' => Product::where('perishable', true)->count(),
                'expiring_soon' => Product::where('perishable', true)
                    ->whereNotNull('expires_at')
                    ->whereDate('expires_at', '<=', now()->addDays(30))
                    ->count(),
            ],
        ]);
    }

    public function perishables(Request $request): View
    {
        $days = max(1, min(365, (int) $request->query('days', 30)));

        return view('warehouse.perishables', [
            'days' => $days,
            'products' => Product::query()
                ->with(['category', 'brand', 'presentation'])
                ->where('perishable', true)
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', '<=', now()->addDays($days))
                ->orderBy('expires_at')
                ->paginate(12)
                ->withQueryString(),
        ]);
    }
}
