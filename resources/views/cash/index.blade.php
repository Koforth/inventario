@extends('layout')

@section('title', 'Caja | Sistema de Inventario')
@section('page-title', 'Caja')
@section('page-subtitle', 'Apertura, cierre y estadisticas del dia')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3 mb-4">
        @foreach ([
            'opening_amount' => 'Monto inicial',
            'income' => 'Ingreso',
            'returns' => 'Devoluciones',
            'loans' => 'Prestamos',
            'expenses' => 'Gastos',
            'total_income' => 'Ingresos totales',
            'egress' => 'Egresos',
            'balance' => 'Saldo',
        ] as $key => $label)
            <div class="col-sm-6 col-xl-3">
                <div class="metric-card p-4 h-100">
                    <div class="text-muted fw-semibold small text-uppercase">{{ $label }}</div>
                    <div class="h3 mb-0 {{ $key === 'balance' ? 'text-success' : '' }}">C$ {{ number_format((float) $stats[$key], 2) }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="table-card p-3 p-md-4">
                @if (! $register)
                    <h2 class="h5 mb-3">Abrir caja</h2>
                    <form method="POST" action="{{ route('cash.open') }}" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label class="form-label fw-semibold">Monto inicial</label>
                            <input name="opening_amount" type="number" min="0" step="0.01" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Notas</label>
                            <textarea name="notes" rows="3" class="form-control"></textarea>
                        </div>
                        <button class="btn btn-success">Abrir caja</button>
                    </form>
                @else
                    <h2 class="h5 mb-1">Caja abierta</h2>
                    <div class="text-muted small mb-3">{{ $register->opened_at->format('d/m/Y h:i A') }}</div>

                    <form method="POST" action="{{ route('cash.movements.store', $register) }}" class="d-grid gap-3 mb-4">
                        @csrf
                        <div>
                            <label class="form-label fw-semibold">Movimiento</label>
                            <select name="type" class="form-select" required>
                                <option value="income">Ingreso</option>
                                <option value="return">Devolucion</option>
                                <option value="loan">Prestamo</option>
                                <option value="expense">Gasto</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Monto</label>
                            <input name="amount" type="number" min="0.01" step="0.01" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Descripcion</label>
                            <input name="description" class="form-control">
                        </div>
                        <button class="btn btn-primary">Registrar movimiento</button>
                    </form>

                    <form method="POST" action="{{ route('cash.close', $register) }}" class="d-grid gap-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="form-label fw-semibold">Monto de cierre</label>
                            <input name="closing_amount" type="number" min="0" step="0.01" value="{{ number_format((float) $stats['balance'], 2, '.', '') }}" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Notas de cierre</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                        </div>
                        <button class="btn btn-outline-danger">Cerrar caja</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="col-lg-8">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <h2 class="h5 mb-1">Movimientos</h2>
                    <div class="text-muted small">Ventas y movimientos manuales de la caja abierta.</div>
                </div>
                @if ($register)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Hora</th>
                                    <th>Tipo</th>
                                    <th>Descripcion</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movements as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at->format('h:i A') }}</td>
                                        <td class="text-capitalize">{{ $movement->type }}</td>
                                        <td>{{ $movement->description ?: '-' }}</td>
                                        <td class="text-end fw-bold">C$ {{ number_format((float) $movement->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-5 text-center text-muted">No hay movimientos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-top px-3 px-md-4 py-3">
                        {{ $movements->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <div class="p-4 text-center text-muted">Abre caja para registrar movimientos.</div>
                @endif
            </div>
        </div>
    </div>
@endsection
