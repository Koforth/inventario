@extends('layout')

@section('title', 'Cotizaciones | Sistema de Inventario')
@section('page-title', 'Cotizaciones')
@section('page-subtitle', 'Consulta de cotizaciones realizadas entre fechas')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('quotes.create') }}" class="btn btn-primary">Generar cotizacion</a>
    </div>

    <div class="table-card">
        <div class="border-bottom p-3 p-md-4">
            <form method="GET" action="{{ route('quotes.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="date_from" class="form-label fw-semibold">Desde</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label fw-semibold">Hasta</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $dateTo }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label fw-semibold">Buscar</label>
                    <input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Numero, cliente, correo o telefono">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Consultar</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Numero</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotes as $quote)
                        <tr>
                            <td class="fw-bold">{{ $quote->number }}</td>
                            <td>
                                <div>{{ $quote->customer_name }}</div>
                                <div class="text-muted small">{{ $quote->customer_phone ?: $quote->customer_email }}</div>
                            </td>
                            <td>{{ $quote->created_at->format('d/m/Y h:i A') }}</td>
                            <td>{{ $quote->user?->name ?? 'Sin usuario' }}</td>
                            <td class="text-end fw-bold">C$ {{ number_format((float) $quote->total, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('quotes.show', $quote) }}" class="btn btn-outline-primary btn-sm">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-muted">
                                No hay cotizaciones para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-top px-3 px-md-4 py-3">
            {{ $quotes->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
