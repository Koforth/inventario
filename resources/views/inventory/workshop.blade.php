@extends('layout')

@section('title', 'Ordenes de Taller | Sistema de Inventario')
@section('page-title', 'Orden de Taller')
@section('page-subtitle', 'Registro y seguimiento de servicios de reparacion y mantenimiento')

@section('content')
    <div class="row g-4">
        <div class="col-xl-5">
            <div class="table-card p-3 p-md-4">
                <h2 class="h5 mb-3">Nueva orden</h2>
                <form method="POST" action="{{ route('inventory.workshop.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cliente</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Seleccione</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tecnico</label>
                        <select name="technician_id" class="form-select">
                            <option value="">Sin asignar</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label fw-semibold">Aparato</label><input name="device_name" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Marca</label><input name="brand" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Modelo</label><input name="model" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Serie</label><input name="serial" class="form-control"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Averia</label><textarea name="issue" rows="2" class="form-control" required></textarea></div>
                    <div class="col-12"><label class="form-label fw-semibold">Observaciones</label><textarea name="observations" rows="2" class="form-control"></textarea></div>
                    <div class="col-12"><label class="form-label fw-semibold">Costo servicio</label><input name="service_cost" type="number" min="0" step="0.01" class="form-control" value="0" required></div>
                    <div class="col-12 d-grid"><button class="btn btn-primary">Registrar orden</button></div>
                </form>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <form method="GET" action="{{ route('inventory.workshop.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-9"><label class="form-label fw-semibold">Buscar orden</label><input name="search" value="{{ $search }}" class="form-control" placeholder="Numero, cliente, aparato, marca o modelo"></div>
                        <div class="col-md-3 d-grid"><button class="btn btn-outline-primary">Buscar</button></div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Orden</th><th>Cliente / Tecnico</th><th>Equipo</th><th>Averia</th><th class="text-end">Costo</th></tr></thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $order->number }}<div class="small text-muted text-capitalize">{{ str_replace('_', ' ', $order->status) }}</div></td>
                                    <td>{{ $order->customer?->name }}<div class="small text-muted">{{ $order->technician?->name ?: 'Sin tecnico' }}</div></td>
                                    <td>{{ $order->device_name }}<div class="small text-muted">{{ $order->brand }} {{ $order->model }} {{ $order->serial ? '- ' . $order->serial : '' }}</div></td>
                                    <td class="small">{{ $order->issue }}<div class="text-muted">{{ $order->observations }}</div></td>
                                    <td class="text-end fw-bold">C$ {{ number_format((float) $order->service_cost, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-center text-muted">No hay ordenes registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-top px-3 px-md-4 py-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection

