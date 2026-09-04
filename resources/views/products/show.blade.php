@extends('layout')

@section('title', $product->nombre . ' | SmartZone')
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
            @can('products.manage')
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">Editar</a>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                @if ($product->images->isNotEmpty())
                    <div id="productDetailImagesCarousel" class="carousel slide" data-bs-interval="false">
                        <div class="carousel-inner">
                            @foreach ($product->images as $image)
                                <div class="carousel-item @if ($loop->first) active @endif">
                                    <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $product->nombre }}" class="d-block w-100" style="max-height: 360px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>

                        @if ($product->images->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#productDetailImagesCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Imagen anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#productDetailImagesCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Imagen siguiente</span>
                            </button>
                        @endif
                    </div>
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
                            <div class="text-muted small fw-semibold text-uppercase">Presentacion</div>
                            <div>{{ $product->presentation?->nombre ?? 'Sin presentacion' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold text-uppercase">Codigo de barras</div>
                            <div>{{ $product->barcode ?: 'Sin codigo' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold text-uppercase">Proveedores</div>
                            <div>{{ $product->supplierNames() }}</div>
                        </div>
                    </div>

                    <hr>

                    <h3 class="h6 text-muted text-uppercase">Descripcion</h3>
                    <p class="mb-0">{{ $product->descripcion ?: 'Sin descripcion registrada.' }}</p>

                    <hr>

                    <h3 class="h6 text-muted text-uppercase">Condiciones</h3>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge {{ $product->taxable ? 'text-bg-info' : 'text-bg-secondary' }}">
                            {{ $product->taxable ? 'Sujeto a impuesto' : 'Sin impuesto' }}
                        </span>
                        <span class="badge {{ $product->perishable ? 'text-bg-warning' : 'text-bg-secondary' }}">
                            {{ $product->perishable ? 'Perecedero' : 'No perecedero' }}
                        </span>
                        <span class="badge {{ $product->inventoryable ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $product->inventoryable ? 'Inventariable' : 'No inventariable' }}
                        </span>
                        @if ($product->expires_at)
                            <span class="badge text-bg-danger">Vence {{ $product->expires_at->format('d/m/Y') }}</span>
                        @endif
                    </div>
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
                                <div class="text-muted small fw-semibold text-uppercase">Venta 1</div>
                                <div class="h4 mb-0">C$ {{ number_format($product->primaryPrice(), 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 bg-light rounded p-3">
                        <div class="text-muted small fw-semibold text-uppercase">Precio de compra</div>
                        <div class="h4 mb-0">C$ {{ number_format((float) $product->purchase_price, 2) }}</div>
                    </div>

                    <div class="mt-3 bg-light rounded p-3">
                        <div class="text-muted small fw-semibold text-uppercase">Precios alternos</div>
                        @foreach ($product->prices->where('is_active', true)->skip(1) as $price)
                            <div>{{ $price->label }}: C$ {{ number_format((float) $price->amount, 2) }}</div>
                        @endforeach
                    </div>

                    <div class="mt-3 bg-light rounded p-3">
                        <div class="text-muted small fw-semibold text-uppercase">Valor en inventario</div>
                        <div class="h4 mb-0">C$ {{ number_format($product->stock * (float) $product->purchase_price, 2) }}</div>
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
