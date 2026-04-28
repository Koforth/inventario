@extends('layout')

@section('title', 'Reportes | Sistema de Inventario')
@section('page-title', 'Reportes de inventario')
@section('page-subtitle', 'Movimientos, ventas, entradas y alertas de stock')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Movimientos</div>
                <div class="display-6 fw-bold">{{ $stats['total_movements'] }}</div>
                <div class="text-muted small">Segun filtros aplicados</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Unidades vendidas</div>
                <div class="display-6 fw-bold text-success">{{ $stats['total_sales'] }}</div>
                <div class="text-muted small">C$ {{ number_format($stats['sales_value'], 2) }} en ventas</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Entradas</div>
                <div class="display-6 fw-bold text-primary">{{ $stats['total_entries'] }}</div>
                <div class="text-muted small">Unidades agregadas</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Bajo stock</div>
                <div class="display-6 fw-bold text-warning">{{ $stats['low_stock'] }}</div>
                <div class="text-muted small">Valor total: C$ {{ number_format($stats['inventory_value'], 2) }}</div>
            </div>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="border-bottom p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="h5 mb-1">Movimientos</h2>
                    <div class="text-muted small">Detalle filtrado de entradas, ventas, mermas y traslados.</div>
                </div>
                <a
                    href="{{ route('reports.export', request()->only(['tipo', 'date_from', 'date_to', 'search'])) }}"
                    class="btn btn-success w-100 w-sm-auto"
                >
                    Descargar Excel
                </a>
            </div>
            <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="tipo" class="form-label fw-semibold">Tipo de movimiento</label>
                    <select id="tipo" name="tipo" class="form-select">
                        <option value="todos" @selected($tipo === 'todos')>Todos</option>
                        <option value="entrada" @selected($tipo === 'entrada')>Entradas</option>
                        <option value="venta" @selected($tipo === 'venta')>Ventas</option>
                        <option value="merma" @selected($tipo === 'merma')>Mermas</option>
                        <option value="traslado" @selected($tipo === 'traslado')>Traslados</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label fw-semibold">Desde</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label fw-semibold">Hasta</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $dateTo }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label fw-semibold">Buscar</label>
                    <input
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Producto, SKU, codigo o usuario"
                    >
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-end">Cantidad</th>
                        <th>Usuario</th>
                        <th class="text-end">Precio actual</th>
                        <th class="text-end">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        @php
                            $typeClasses = [
                                'entrada' => 'text-bg-primary',
                                'venta' => 'text-bg-success',
                                'merma' => 'text-bg-danger',
                                'traslado' => 'text-bg-secondary',
                            ];
                            $price = (float) ($movement->product?->precio ?? 0);
                            $amount = $movement->tipo === 'venta' ? $price * $movement->cantidad : null;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $movement->created_at->format('d/m/Y') }}</div>
                                <div class="text-muted small">{{ $movement->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if ($movement->product)
                                    <a href="{{ route('products.show', $movement->product) }}" class="fw-bold text-decoration-none">
                                        {{ $movement->product->nombre }}
                                    </a>
                                    <div class="text-muted small">
                                        SKU: {{ $movement->product->sku }}
                                        @if ($movement->product->brand)
                                            <span class="ms-2">{{ $movement->product->brand->nombre }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">Producto eliminado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $typeClasses[$movement->tipo] ?? 'text-bg-secondary' }} text-capitalize">
                                    {{ $movement->tipo }}
                                </span>
                            </td>
                            <td class="text-end fw-bold">{{ $movement->cantidad }}</td>
                            <td>{{ $movement->user?->name ?? 'Sin usuario' }}</td>
                            <td class="text-end">C$ {{ number_format($price, 2) }}</td>
                            <td class="text-end">
                                @if ($amount !== null)
                                    C$ {{ number_format($amount, 2) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-5 text-center text-muted">
                                No hay movimientos para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-top px-3 px-md-4 py-3">
            {{ $movements->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="table-card h-100">
                <div class="border-bottom p-3 p-md-4">
                    <h2 class="h5 mb-1">Productos con stock bajo</h2>
                    <div class="text-muted small">Productos cuyo stock actual esta en o debajo del minimo.</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Categoria</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Minimo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowStockProducts as $product)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $product) }}" class="fw-bold text-decoration-none">
                                            {{ $product->nombre }}
                                        </a>
                                        <div class="text-muted small">{{ $product->brand?->nombre ?? 'Sin marca' }}</div>
                                    </td>
                                    <td>{{ $product->category?->nombre ?? 'Sin categoria' }}</td>
                                    <td class="text-center fw-bold text-warning">{{ $product->stock }}</td>
                                    <td class="text-center">{{ $product->stock_minimo }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-muted">
                                        No hay productos con stock bajo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="table-card h-100">
                <div class="border-bottom p-3 p-md-4">
                    <h2 class="h5 mb-1">Mas vendidos</h2>
                    <div class="text-muted small">Ranking por unidades vendidas en el periodo seleccionado.</div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($topSellingProducts as $item)
                        <div class="list-group-item px-3 px-md-4 py-3">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-bold">{{ $item->product?->nombre ?? 'Producto eliminado' }}</div>
                                    <div class="text-muted small">{{ $item->product?->sku ?? 'Sin SKU' }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="h5 mb-0">{{ (int) $item->total_quantity }}</div>
                                    <div class="text-muted small">unidades</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            Todavia no hay ventas registradas.
                        </div>
                    @endforelse
                </div>
                <div class="border-top p-3 p-md-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small text-uppercase fw-semibold">Mermas</div>
                            <div class="h4 mb-0 text-danger">{{ $stats['total_losses'] }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small text-uppercase fw-semibold">Traslados</div>
                            <div class="h4 mb-0 text-secondary">{{ $stats['total_transfers'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
