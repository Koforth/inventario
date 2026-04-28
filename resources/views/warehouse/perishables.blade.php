@extends('layout')

@section('title', 'Productos perecederos | Almacen')
@section('page-title', 'Productos perecederos')
@section('page-subtitle', 'Consulta de productos proximos a vencer')

@section('content')
    <div class="table-card">
        <div class="border-bottom p-3 p-md-4">
            <form method="GET" action="{{ route('warehouse.perishables') }}" class="row g-3 align-items-end">
                <div class="col-md-10">
                    <label for="days" class="form-label fw-semibold">Vencen en los proximos dias</label>
                    <input id="days" name="days" type="number" min="1" max="365" value="{{ $days }}" class="form-control">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Consultar</button>
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
                        <th class="text-center">Stock</th>
                        <th class="text-end">Vence</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $product) }}" class="fw-bold text-decoration-none">{{ $product->nombre }}</a>
                                <div class="text-muted small">SKU: {{ $product->sku }}</div>
                            </td>
                            <td>{{ $product->category?->nombre ?? 'Sin categoria' }}</td>
                            <td>{{ $product->brand?->nombre ?? 'Sin marca' }}</td>
                            <td>{{ $product->presentation?->nombre ?? 'Sin presentacion' }}</td>
                            <td class="text-center fw-bold">{{ $product->stock }}</td>
                            <td class="text-end">
                                <span class="badge text-bg-danger">{{ $product->expires_at->format('d/m/Y') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-muted">
                                No hay productos perecederos proximos a vencer.
                            </td>
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
