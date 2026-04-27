@extends('layout')

@section('title', 'Home | Sistema de Inventario')
@section('page-title', 'Bienvenido')
@section('page-subtitle', 'Panel principal del sistema de inventario')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="text-uppercase text-primary fw-bold small mb-2">Home</p>
                    <h1 class="display-6 fw-bold mb-3">Bienvenido, {{ auth()->user()->name }}</h1>
                    <p class="lead text-muted mb-4">
                        Desde aqui puedes consultar el catalogo, administrar productos y registrar movimientos segun tu rol.
                    </p>
                    <div class="d-grid d-sm-flex gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-primary">Ver productos</a>
                        @if (auth()->user()->role === 'admin')
                            <a href="{{ route('products.create') }}" class="btn btn-outline-primary">Crear producto</a>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="bg-light rounded-3 p-4">
                        <div class="text-muted small fw-semibold text-uppercase">Sesion activa</div>
                        <div class="h5 mb-1">{{ auth()->user()->email }}</div>
                        <span class="badge text-bg-success text-capitalize">{{ auth()->user()->role }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
