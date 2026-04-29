<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Technician;
use App\Models\WorkshopOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkshopController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $orders = WorkshopOrder::query()
            ->with(['customer', 'technician'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('number', 'like', "%{$search}%")
                        ->orWhere('device_name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('inventory.workshop', [
            'search' => $search,
            'orders' => $orders,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'technicians' => Technician::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'technician_id' => ['nullable', 'exists:technicians,id'],
            'device_name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial' => ['nullable', 'string', 'max:255'],
            'issue' => ['required', 'string', 'max:2000'],
            'observations' => ['nullable', 'string', 'max:2000'],
            'service_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $data['number'] = 'OT-' . now()->format('Ymd-His') . '-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
        WorkshopOrder::create($data);

        return back()->with('success', 'Orden de taller registrada.');
    }
}

