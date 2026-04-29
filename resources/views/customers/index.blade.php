@extends('layout')

@section('title', 'Clientes | Sistema de Inventario')
@section('page-title', 'Clientes')
@section('page-subtitle', 'Gestion de clientes para ventas al contado o credito')

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="table-card p-3 p-md-4">
                <h2 class="h5 mb-3">Nuevo cliente</h2>
                <form method="POST" action="{{ route('customers.store') }}" class="row g-3">
                    @csrf
                    @include('customers.partials.form-fields', ['customer' => null])
                    <div class="col-12 d-grid">
                        <button class="btn btn-primary">Guardar cliente</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <form method="GET" action="{{ route('customers.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label for="search" class="form-label fw-semibold">Buscar cliente</label>
                            <input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nombre, empresa, DNI, RUC, telefono o correo">
                        </div>
                        <div class="col-md-3 d-grid">
                            <button class="btn btn-outline-primary">Buscar</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Nombre</th>
                                <th>DNI/RUC</th>
                                <th>Contacto</th>
                                <th class="text-end">Limite crediticio</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $customer)
                                <tr>
                                    <td>
                                        <span class="badge text-bg-primary">{{ $customer->typeLabel() }}</span>
                                    </td>
                                    <td class="fw-bold">{{ $customer->name }}</td>
                                    <td>{{ $customer->dni ?: '-' }} / {{ $customer->ruc ?: '-' }}</td>
                                    <td>{{ $customer->phone ?: '-' }}<div class="text-muted small">{{ $customer->email ?: 'Sin correo' }}</div></td>
                                    <td class="text-end">C$ {{ number_format((float) $customer->credit_limit, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $customer->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $customer->is_active ? 'Vigente' : 'Inactivo' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-5 text-center text-muted">No hay clientes registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-top px-3 px-md-4 py-3">
                    {{ $customers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
