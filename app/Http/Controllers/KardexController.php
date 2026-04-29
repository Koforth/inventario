<?php

namespace App\Http\Controllers;

use App\Models\InventoryPeriod;
use App\Models\Movement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KardexController extends Controller
{
    public function index(Request $request): View
    {
        $period = $this->ensureOpenPeriod();
        $productId = (int) $request->query('product_id', 0);
        $dateFrom = (string) $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = (string) $request->query('date_to', now()->toDateString());

        $products = Product::orderBy('nombre')->get();

        $movements = Movement::query()
            ->with(['product', 'user'])
            ->when($productId > 0, fn ($query) => $query->where('product_id', $productId))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = Product::query()
            ->when($productId > 0, fn ($query) => $query->whereKey($productId))
            ->selectRaw('COUNT(*) as products')
            ->selectRaw('COALESCE(SUM(stock), 0) as stock')
            ->first();

        return view('inventory.kardex', [
            'period' => $period,
            'products' => $products,
            'movements' => $movements,
            'productId' => $productId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'summary' => $summary,
        ]);
    }

    public function move(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'operation' => ['required', 'in:entrada,salida'],
            'quantity' => ['required', 'integer', 'min:1'],
            'exit_type' => ['nullable', 'in:merma,traslado'],
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            $quantity = (int) $data['quantity'];

            if ($data['operation'] === 'entrada') {
                $product->increment('stock', $quantity);
                Movement::create([
                    'product_id' => $product->id,
                    'tipo' => 'entrada',
                    'cantidad' => $quantity,
                    'user_id' => auth()->id(),
                ]);
                return;
            }

            if ($product->stock < $quantity) {
                abort(422, 'No hay stock suficiente para salida.');
            }

            $product->decrement('stock', $quantity);
            Movement::create([
                'product_id' => $product->id,
                'tipo' => $data['exit_type'] ?? 'traslado',
                'cantidad' => $quantity,
                'user_id' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Movimiento de kardex registrado.');
    }

    private function ensureOpenPeriod(): InventoryPeriod
    {
        $key = now()->format('Y-m');

        return InventoryPeriod::firstOrCreate(
            ['period_key' => $key],
            [
                'starts_at' => now()->startOfMonth()->toDateString(),
                'ends_at' => now()->endOfMonth()->toDateString(),
                'status' => 'open',
            ]
        );
    }
}

