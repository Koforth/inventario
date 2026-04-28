@extends('layout')

@section('title', 'Almacen | Sistema de Inventario')
@section('page-title', 'Almacen')
@section('page-subtitle', 'Catalogos, productos y control de perecederos')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <a href="{{ route('warehouse.categories.index') }}" class="metric-card p-4 h-100 d-block text-decoration-none text-dark">
                <div class="text-muted fw-semibold small text-uppercase">Categorias</div>
                <div class="display-6 fw-bold">{{ $stats['categories'] }}</div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-4">
            <a href="{{ route('warehouse.presentations.index') }}" class="metric-card p-4 h-100 d-block text-decoration-none text-dark">
                <div class="text-muted fw-semibold small text-uppercase">Presentaciones</div>
                <div class="display-6 fw-bold">{{ $stats['presentations'] }}</div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-4">
            <a href="{{ route('warehouse.brands.index') }}" class="metric-card p-4 h-100 d-block text-decoration-none text-dark">
                <div class="text-muted fw-semibold small text-uppercase">Marcas</div>
                <div class="display-6 fw-bold">{{ $stats['brands'] }}</div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-4">
            <a href="{{ route('products.index') }}" class="metric-card p-4 h-100 d-block text-decoration-none text-dark">
                <div class="text-muted fw-semibold small text-uppercase">Productos</div>
                <div class="display-6 fw-bold">{{ $stats['products'] }}</div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Perecederos</div>
                <div class="display-6 fw-bold text-warning">{{ $stats['perishables'] }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <a href="{{ route('warehouse.perishables') }}" class="metric-card p-4 h-100 d-block text-decoration-none text-dark">
                <div class="text-muted fw-semibold small text-uppercase">Por vencer</div>
                <div class="display-6 fw-bold text-danger">{{ $stats['expiring_soon'] }}</div>
            </a>
        </div>
    </div>
@endsection
