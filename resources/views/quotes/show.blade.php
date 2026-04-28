@extends('layout')

@section('title', $quote->number . ' | Cotizaciones')
@section('page-title', $quote->number)
@section('page-subtitle', 'Detalle de cotizacion')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <h2 class="h5 mb-1">{{ $quote->customer_name }}</h2>
                            <div class="text-muted small">
                                {{ $quote->customer_phone ?: 'Sin telefono' }}
                                @if ($quote->customer_email)
                                    <span class="ms-2">{{ $quote->customer_email }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">{{ $quote->created_at->format('d/m/Y') }}</div>
                            <div class="text-muted small">{{ $quote->user?->name ?? 'Sin usuario' }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Impuesto</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($quote->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $item->product_name }}</div>
                                        <div class="text-muted small">SKU: {{ $item->sku ?: '-' }}</div>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">C$ {{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="text-end">C$ {{ number_format((float) $item->line_tax, 2) }}</td>
                                    <td class="text-end fw-bold">C$ {{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="table-card p-3 p-md-4">
                <h2 class="h5 mb-3">Resumen</h2>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <strong>C$ {{ number_format((float) $quote->subtotal, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Impuesto</span>
                    <strong>C$ {{ number_format((float) $quote->tax, 2) }}</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between h4">
                    <span>Total</span>
                    <strong>C$ {{ number_format((float) $quote->total, 2) }}</strong>
                </div>

                @if ($quote->notes)
                    <hr>
                    <div class="text-muted small fw-semibold text-uppercase">Notas</div>
                    <p class="mb-0">{{ $quote->notes }}</p>
                @endif

                <div class="d-grid mt-4">
                    <a href="{{ route('quotes.index') }}" class="btn btn-outline-secondary">Volver</a>
                </div>
            </div>
        </div>
    </div>
@endsection
