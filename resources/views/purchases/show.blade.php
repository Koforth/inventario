@extends('layout')

@section('title', $purchase->number . ' | Compras')
@section('page-title', $purchase->number)
@section('page-subtitle', 'Detalle de compra')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4"><h2 class="h5 mb-1">{{ $purchase->supplier?->name }}</h2><div class="text-muted small">{{ $purchase->created_at->format('d/m/Y h:i A') }} - {{ $purchase->payment_type }}</div></div>
                <div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-end">Costo</th><th class="text-end">Total</th></tr></thead><tbody>@foreach($purchase->items as $item)<tr><td><div class="fw-bold">{{ $item->product_name }}</div><div class="text-muted small">{{ $item->barcode ?: $item->sku }}</div></td><td class="text-center">{{ $item->quantity }}</td><td class="text-end">C$ {{ number_format((float)$item->unit_cost,2) }}</td><td class="text-end fw-bold">C$ {{ number_format((float)$item->line_total,2) }}</td></tr>@endforeach</tbody></table></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="table-card p-3 p-md-4">
                <h2 class="h5 mb-3">Resumen</h2>
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>C$ {{ number_format((float)$purchase->subtotal,2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Impuesto</span><strong>C$ {{ number_format((float)$purchase->tax,2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Pagado</span><strong>C$ {{ number_format((float)$purchase->paid_amount,2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Saldo</span><strong>C$ {{ number_format((float)$purchase->balance,2) }}</strong></div>
                <hr><div class="d-flex justify-content-between h4"><span>Total</span><strong>C$ {{ number_format((float)$purchase->total,2) }}</strong></div>
                @if($purchase->balance > 0)
                    <hr><form method="POST" action="{{ route('purchases.payments.store', $purchase) }}" class="d-grid gap-2">@csrf<label class="form-label fw-semibold">Abono</label><input name="amount" type="number" min="0.01" max="{{ $purchase->balance }}" step="0.01" class="form-control" required><input name="payment_date" type="date" value="{{ today()->format('Y-m-d') }}" class="form-control" required><input name="notes" class="form-control" placeholder="Nota"><button class="btn btn-success">Registrar abono</button></form>
                @endif
            </div>
        </div>
    </div>
@endsection
