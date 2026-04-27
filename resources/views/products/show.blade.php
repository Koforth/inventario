@extends('layout')

@section('title', $product->nombre . ' | Sistema de Inventario')
@section('page-title', $product->nombre)
@section('page-subtitle', 'Detalle del producto')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-primary fw-bold small text-uppercase">SKU {{ $product->sku }}</div>
            <h1 class="h3 mb-0">{{ $product->nombre }}</h1>
        </div>
        <div class="d-grid d-sm-flex gap-2 w-100 w-sm-auto">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Volver</a>
            @if (auth()->user()->role === 'admin')
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">Editar</a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                @if ($product->image_path)
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->nombre }}" class="card-img-top" style="max-height: 320px; object-fit: cover;">
                @endif
                <div class="card-body p-4">
                    <h2 class="h5 mb-4">Informacion general</h2>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold text-uppercase">Categoria</div>
                            <div>{{ $product->category?->nombre ?? 'Sin categoria' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold text-uppercase">Marca</div>
                            <div>{{ $product->brand?->nombre ?? 'Sin marca' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold text-uppercase">Codigo de barras</div>
                            <div>{{ $product->barcode ?: 'Sin codigo' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold text-uppercase">Proveedor</div>
                            <div>{{ $product->proveedor ?: 'Sin proveedor' }}</div>
                        </div>
                    </div>

                    <hr>

                    <h3 class="h6 text-muted text-uppercase">Descripcion</h3>
                    <p class="mb-0">{{ $product->descripcion ?: 'Sin descripcion registrada.' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h5 mb-4">Existencias</h2>

                    <div class="mb-3">
                        <div class="text-muted small fw-semibold text-uppercase">Stock actual</div>
                        <div class="display-5 fw-bold {{ $product->isLowStock() ? 'text-warning' : 'text-success' }}">
                            {{ $product->stock }}
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <div class="text-muted small fw-semibold text-uppercase">Minimo</div>
                                <div class="h4 mb-0">{{ $product->stock_minimo }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <div class="text-muted small fw-semibold text-uppercase">Precio</div>
                                <div class="h4 mb-0">C$ {{ number_format((float) $product->precio, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 bg-light rounded p-3">
                        <div class="text-muted small fw-semibold text-uppercase">Valor en inventario</div>
                        <div class="h4 mb-0">C$ {{ number_format($product->stock * (float) $product->precio, 2) }}</div>
                    </div>

                    @if ($product->isLowStock())
                        <div class="alert alert-warning mt-4 mb-0">
                            Este producto necesita reposicion.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
