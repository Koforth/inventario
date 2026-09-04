@extends('layout')

@section('title', 'Catalogo de Productos | SmartZone')
@section('page-title', 'Catalogo de Productos (Maestro - Detalle)')
@section('page-subtitle', 'Inventario completo agrupado por categoria')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Productos</div>
                <div class="display-6 fw-bold">{{ $stats['total'] }}</div>
                <div class="text-muted small">Registrados en catalogo</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Valor inventario</div>
                <div class="display-6 fw-bold text-success">C$ {{ number_format($stats['inventory_value'], 2) }}</div>
                <div class="text-muted small">Costo de compra x stock</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Categorias</div>
                <div class="display-6 fw-bold">{{ $stats['categories'] }}</div>
                <div class="text-muted small">Clasificaciones activas</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Marcas</div>
                <div class="display-6 fw-bold">{{ \App\Models\Brand::count() }}</div>
                <div class="text-muted small">Marcas registradas</div>
            </div>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="border-bottom p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="h5 mb-1">Catalogo</h2>
                    <div class="text-muted small">Productos con detalle de stock, precio y proveedor.</div>
                </div>
                <a href="{{ route('reports.catalog.export') }}" class="btn btn-success w-100 w-sm-auto">
                    Descargar Excel
                </a>
            </div>
            <form method="GET" action="{{ route('reports.catalog') }}" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="search" class="form-label fw-semibold">Buscar</label>
                    <input id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nombre, SKU o codigo de barras">
                </div>
                <div class="col-md-4 d-grid">
                    <button class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Categoria</th>
                        <th>Marca</th>
                        <th>Presentacion</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Minimo</th>
                        <th>Proveedores</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $product) }}" class="fw-bold text-decoration-none">{{ $product->nombre }}</a>
                                <div class="text-muted small">SKU: {{ $product->sku }} | {{ $product->barcode ? 'Cod: ' . $product->barcode : '' }}</div>
                            </td>
                            <td>{{ $product->category?->nombre ?? 'Sin categoria' }}</td>
                            <td>{{ $product->brand?->nombre ?? 'N/A' }}</td>
                            <td>{{ $product->presentation?->nombre ?? 'N/A' }}</td>
                            <td class="text-end fw-semibold">C$ {{ number_format($product->primaryPrice(), 2) }}</td>
                            <td class="text-center {{ $product->isLowStock() ? 'fw-bold text-danger' : '' }}">{{ $product->stock }}</td>
                            <td class="text-center">{{ $product->stock_minimo }}</td>
                            <td class="small">{{ $product->supplierNames() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-5 text-center text-muted">No hay productos para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-top px-3 px-md-4 py-3">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection