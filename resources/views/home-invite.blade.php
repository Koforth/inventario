@extends('layout')

@section('title', 'Bienvenida | Sistema de Inventario')
@section('page-title', 'Bienvenida')
@section('page-subtitle', 'Informacion general del sistema')

@section('content')
    <div class="table-card p-4 p-md-5 mb-4">
        <h1 class="h3 fw-bold mb-3">Bienvenido a Sistema de Inventario</h1>
        <p class="text-muted mb-0">
            Esta plataforma te permite consultar el catalogo de productos, revisar informacion de inventario y
            navegar por los modulos disponibles segun tus permisos.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted small text-uppercase fw-semibold mb-2">Catalogo</div>
                <p class="mb-0">Consulta productos con imagenes, precios, presentacion, marca y categoria.</p>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted small text-uppercase fw-semibold mb-2">Navegacion</div>
                <p class="mb-0">Usa el menu lateral para entrar rapido a las secciones habilitadas para tu rol.</p>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted small text-uppercase fw-semibold mb-2">Sesion</div>
                <p class="mb-1"><strong>{{ auth()->user()->name }}</strong></p>
                <p class="text-muted mb-0">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
@endsection

