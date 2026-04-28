@extends('layout')

@section('title', 'Compras al credito | Compras')
@section('page-title', 'Compras al credito')
@section('page-subtitle', 'Seguimiento de saldos y abonos pendientes')

@section('content')
    <div class="table-card">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Numero</th><th>Proveedor</th><th>Fecha</th><th class="text-end">Total</th><th class="text-end">Pagado</th><th class="text-end">Saldo</th><th></th></tr></thead><tbody>
            @forelse($purchases as $purchase)
                <tr><td class="fw-bold">{{ $purchase->number }}</td><td>{{ $purchase->supplier?->name }}</td><td>{{ $purchase->created_at->format('d/m/Y') }}</td><td class="text-end">C$ {{ number_format((float)$purchase->total,2) }}</td><td class="text-end">C$ {{ number_format((float)$purchase->paid_amount,2) }}</td><td class="text-end fw-bold text-danger">C$ {{ number_format((float)$purchase->balance,2) }}</td><td class="text-end"><a href="{{ route('purchases.show',$purchase) }}" class="btn btn-outline-primary btn-sm">Abonar</a></td></tr>
            @empty
                <tr><td colspan="7" class="py-5 text-center text-muted">No hay compras al credito pendientes.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="border-top px-3 px-md-4 py-3">{{ $purchases->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
