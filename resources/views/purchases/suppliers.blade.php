@extends('layout')

@section('title', 'Proveedores | Compras')
@section('page-title', 'Proveedores')
@section('page-subtitle', 'Nombre, DNI, RUC y datos de contacto')

@section('content')
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="table-card p-3 p-md-4">
                <h2 class="h5 mb-3">Nuevo proveedor</h2>
                <form method="POST" action="{{ route('suppliers.store') }}" class="row g-3">
                    @csrf
                    @include('purchases.supplier-fields', ['supplier' => new App\Models\Supplier()])
                    <div class="col-12 d-grid"><button class="btn btn-primary">Guardar</button></div>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-10"><label class="form-label fw-semibold">Buscar</label><input name="search" value="{{ $search }}" class="form-control"></div>
                        <div class="col-md-2 d-grid"><button class="btn btn-primary">Buscar</button></div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Proveedor</th><th>Documento</th><th>Contacto</th><th class="text-end">Acciones</th></tr></thead>
                        <tbody>
                            @forelse ($suppliers as $supplier)
                                <tr>
                                    <td class="fw-bold">{{ $supplier->name }}</td>
                                    <td>DNI: {{ $supplier->dni ?: '-' }}<br>RUC: {{ $supplier->ruc ?: '-' }}</td>
                                    <td>{{ $supplier->contact_name ?: '-' }}<br><span class="text-muted small">{{ $supplier->phone }} {{ $supplier->email }}</span></td>
                                    <td class="text-end"><button class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#supplier{{ $supplier->id }}">Editar</button></td>
                                </tr>
                                <tr class="collapse" id="supplier{{ $supplier->id }}">
                                    <td colspan="4" class="bg-light">
                                        <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="row g-3">
                                            @csrf @method('PUT')
                                            @include('purchases.supplier-fields', ['supplier' => $supplier])
                                            <div class="col-md-6 d-grid"><button class="btn btn-primary">Actualizar</button></div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-5 text-center text-muted">No hay proveedores para mostrar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-top px-3 px-md-4 py-3">{{ $suppliers->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
