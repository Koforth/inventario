<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CashController extends Controller
{
    public function index(): JsonResponse
    {
        $registers = CashRegister::with('movements.user')->latest('opened_at')->paginate(15);

        $openCash = CashRegister::with('movements')->where('status', 'open')->latest('opened_at')->first();

        $balance = 0.0;
        if ($openCash) {
            $balance = (float) $openCash->movements->sum('amount');
        }

        return response()->json([
            'open_cash' => $openCash,
            'balance' => $balance,
            'history' => $registers,
        ]);
    }

    public function open(Request $request): JsonResponse
    {
        $data = $request->validate([
            'opening_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $lock = Cache::lock('cash_open_' . auth()->id(), 10);

        if (! $lock->get()) {
            return response()->json(['message' => 'Otro proceso esta operando sobre la caja.'], 409);
        }

        try {
            $openCash = CashRegister::where('status', 'open')->latest('opened_at')->first();
            if ($openCash) {
                return response()->json(['message' => 'Ya existe una caja abierta.'], 422);
            }

            $cashRegister = CashRegister::create([
                'opening_amount' => $data['opening_amount'],
                'opened_by' => auth()->id(),
                'opened_at' => now(),
                'status' => 'open',
            ]);

            return response()->json([
                'message' => 'Caja abierta correctamente.',
                'cash_register' => $cashRegister,
            ], 201);
        } finally {
            $lock->release();
        }
    }

    public function close(Request $request, CashRegister $cashRegister): JsonResponse
    {
        $data = $request->validate([
            'closing_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if ($cashRegister->status !== 'open') {
            return response()->json(['message' => 'La caja ya esta cerrada.'], 422);
        }

        $expected = (float) $cashRegister->opening_amount + (float) $cashRegister->movements()->sum('amount');
        $difference = (float) $data['closing_amount'] - $expected;

        $cashRegister->update([
            'closing_amount' => $data['closing_amount'],
            'closed_by' => auth()->id(),
            'closed_at' => now(),
            'status' => 'closed',
            'closing_notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Caja cerrada correctamente.',
            'cash_register' => $cashRegister->fresh(),
            'expected_amount' => round($expected, 2),
            'difference' => round($difference, 2),
        ]);
    }

    public function movement(Request $request, CashRegister $cashRegister): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:sale,return,loan,expense,income'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($cashRegister->status !== 'open') {
            return response()->json(['message' => 'La caja debe estar abierta.'], 422);
        }

        $movement = $cashRegister->movements()->create([
            'type' => $data['type'],
            'amount' => $data['type'] === 'expense' || $data['type'] === 'loan' ? -abs($data['amount']) : abs($data['amount']),
            'description' => $data['description'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Movimiento registrado.',
            'movement' => $movement,
        ], 201);
    }
}