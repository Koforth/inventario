@extends('layout')

@section('title', 'Editar producto | Sistema de Inventario')
@section('page-title', 'Editar producto')
@section('page-subtitle', $product->nombre)

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                @method('PUT')
                @include('products._form')
            </form>
        </div>
    </div>
@endsection
