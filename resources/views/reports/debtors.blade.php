@extends('layout')

@section('title', 'Clientes Morosos | SmartZone')
@section('page-title', 'Clientes Morosos')
@section('page-subtitle', 'Clientes con saldo pendiente en ventas al credito')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Clientes con deuda</div>
                <div class="display-6 fw-bold">{{ $debtors->count() }}</div>
                <div class="text-muted small">Con saldo al credito</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Total pendiente</div>
                <div class="display-6 fw-bold text-danger">C$ {{ number_format($totalPending, 2) }}</div>
                <div class="text-muted small">Suma de saldos por cobrar</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Deuda promedio</div>
                <div class="display-6 fw-bold text-warning">C$ {{ $debtors->isNotEmpty() ? number_format($totalPending / $debtors->count(), 2) : '0.00' }}</div>
                <div class="text-muted small">Promedio por cliente</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Deudor mayor</div>
                <div class="display-6 fw-bold text-heading">{{ $debtors->first()?->customer?->name ?? '-' }}</div>
                <div class="text-muted small">C$ {{ $debtors->isNotEmpty() ? number_format($debtors->first()->balance, 2) : '0.00' }}</div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="border-bottom p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="h5 mb-1">Morosos</h2>
                    <div class="text-muted small">Saldos pendientes por cobrar de ventas al credito.</div>
                </div>
                <a href="{{ route('reports.debtors.export') }}" class="btn btn-success w-100 w-sm-auto">
                    Descargar Excel
                </a>
            </div>
            <form method="GET" action="{{ route('reports.debtors') }}" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="search" class="form-label fw-semibold">Buscar</label>
                    <input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nombre, DNI o RUC">
                </div>
                <div class="col-md-4 d-grid">
                    <button class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cliente</th>
                        <th>Identificacion</th>
                        <th class="text-end">Limite crediticio</th>
                        <th class="text-end">Saldo pendiente</th>
                        <th class="text-center">% de deuda</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($debtors as $item)
                        @php $percent = $item->customer->credit_limit > 0 ? (int) round($item->balance / $item->customer->credit_limit * 100) : 100; @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->customer->name }}</div>
                                <div class="text-muted small">{{ $item->customer->typeLabel() }}</div>
                            </td>
                            <td>{{ $item->customer->dni ?: $item->customer->ruc ?: 'Sin identificacion' }}</td>
                            <td class="text-end">C$ {{ number_format($item->customer->credit_limit, 2) }}</td>
                            <td class="text-end fw-bold text-danger">C$ {{ number_format($item->balance, 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $percent >= 100 ? 'text-bg-danger' : ($percent >= 70 ? 'text-bg-warning' : 'text-bg-secondary') }}">{{ $percent }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5 text-center text-muted">No hay clientes con saldo pendiente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection