<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function print(Sale $sale): View
    {
        $sale->load(['customer', 'items.product', 'user', 'receiptType']);

        return view('sales.print', compact('sale'));
    }
}