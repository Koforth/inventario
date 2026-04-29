<?php

namespace App\Http\Controllers;

use App\Models\ReceiptType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReceiptTypeController extends Controller
{
    public function index(): View
    {
        return view('inventory.receipts', [
            'types' => ReceiptType::latest()->paginate(12),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:receipt_types,code'],
            'prefix' => ['nullable', 'string', 'max:20'],
            'current_number' => ['nullable', 'integer', 'min:0'],
            'padding' => ['nullable', 'integer', 'min:1', 'max:12'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['prefix'] = $data['prefix'] ? strtoupper($data['prefix']) : null;
        $data['current_number'] = (int) ($data['current_number'] ?? 0);
        $data['padding'] = (int) ($data['padding'] ?? 8);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        ReceiptType::create($data);

        return back()->with('success', 'Tipo de comprobante creado.');
    }

    public function update(Request $request, ReceiptType $receiptType): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('receipt_types', 'code')->ignore($receiptType)],
            'prefix' => ['nullable', 'string', 'max:20'],
            'current_number' => ['nullable', 'integer', 'min:0'],
            'padding' => ['nullable', 'integer', 'min:1', 'max:12'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $receiptType->update([
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'prefix' => $data['prefix'] ? strtoupper($data['prefix']) : null,
            'current_number' => (int) ($data['current_number'] ?? 0),
            'padding' => (int) ($data['padding'] ?? 8),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return back()->with('success', 'Tipo de comprobante actualizado.');
    }
}

