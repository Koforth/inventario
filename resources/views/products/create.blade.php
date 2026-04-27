@extends('layout')

@section('title', 'Nuevo producto | Sistema de Inventario')
@section('page-title', 'Nuevo producto')
@section('page-subtitle', 'Registro de producto para inventario')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                @include('products._form')
            </form>
        </div>
    </div>
@endsection
