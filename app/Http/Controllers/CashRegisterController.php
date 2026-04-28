<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashRegisterController extends Controller
{
    public function index(): View
    {
        $register = CashRegister::query()
            ->with('movements')
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        $todayRegisters = CashRegister::query()
            ->with('movements')
            ->whereDate('opened_at', today())
            ->get();

        $movements = $register
            ? $register->movements()->latest()->paginate(12)
            : collect();

        return view('cash.index', [
            'register' => $register,
            'movements' => $movements,
            'stats' => $this->stats($register, $todayRegisters),
        ]);
    }

    public function open(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'opening_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (CashRegister::where('status', 'open')->exists()) {
            return back()->withErrors(['opening_amount' => 'Ya existe una caja abierta.']);
        }

        CashRegister::create([
            'opened_at' => now(),
            'opening_amount' => $data['opening_amount'],
            'notes' => $data['notes'] ?? null,
            'opened_by' => auth()->id(),
        ]);

        return back()->with('success', 'Caja abierta correctamente.');
    }

    public function close(Request $request, CashRegister $cashRegister): RedirectResponse
    {
        $data = $request->validate([
            'closing_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cashRegister->update([
            'closed_at' => now(),
            'closing_amount' => $data['closing_amount'],
            'notes' => trim(($cashRegister->notes ? $cashRegister->notes . PHP_EOL : '') . ($data['notes'] ?? '')) ?: null,
            'status' => 'closed',
            'closed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Caja cerrada correctamente.');
    }

    public function movement(Request $request, CashRegister $cashRegister): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:return,loan,expense,income'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $cashRegister->movements()->create([
            'type' => $data['type'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Movimiento de caja registrado.');
    }

    private function stats(?CashRegister $register, $todayRegisters): array
    {
        $source = $register ? collect([$register]) : $todayRegisters;
        $movements = $source->flatMap->movements;

        $sales = (float) $movements->where('type', 'sale')->sum('amount');
        $returns = (float) $movements->where('type', 'return')->sum('amount');
        $loans = (float) $movements->where('type', 'loan')->sum('amount');
        $expenses = (float) $movements->where('type', 'expense')->sum('amount');
        $manualIncome = (float) $movements->where('type', 'income')->sum('amount');
        $opening = (float) $source->sum('opening_amount');
        $income = $sales + $manualIncome;
        $egress = $returns + $loans + $expenses;

        return [
            'opening_amount' => $opening,
            'income' => $income,
            'returns' => $returns,
            'loans' => $loans,
            'expenses' => $expenses,
            'total_income' => $opening + $income,
            'egress' => $egress,
            'balance' => $opening + $income - $egress,
        ];
    }
}
