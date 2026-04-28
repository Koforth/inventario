<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="12">Reporte de inventario</th>
            </tr>
            <tr>
                <th colspan="12">
                    Generado: {{ $generatedAt->format('d/m/Y h:i A') }}
                    | Tipo: {{ $tipo === 'todos' ? 'Todos' : ucfirst($tipo) }}
                    | Desde: {{ $dateFrom ?: 'Todos' }}
                    | Hasta: {{ $dateTo ?: 'Todos' }}
                    | Busqueda: {{ $search ?: 'Todos' }}
                </th>
            </tr>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Producto</th>
                <th>SKU</th>
                <th>Codigo de barras</th>
                <th>Marca</th>
                <th>Categoria</th>
                <th>Usuario</th>
                <th>Precio actual</th>
                <th>Importe</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $movement)
                @php
                    $price = (float) ($movement->product?->precio ?? 0);
                    $amount = $movement->tipo === 'venta' ? $price * $movement->cantidad : 0;
                @endphp
                <tr>
                    <td>{{ $movement->created_at->format('d/m/Y') }}</td>
                    <td>{{ $movement->created_at->format('h:i A') }}</td>
                    <td>{{ ucfirst($movement->tipo) }}</td>
                    <td>{{ $movement->cantidad }}</td>
                    <td>{{ $movement->product?->nombre ?? 'Producto eliminado' }}</td>
                    <td>{{ $movement->product?->sku ?? '' }}</td>
                    <td>{{ $movement->product?->barcode ?? '' }}</td>
                    <td>{{ $movement->product?->brand?->nombre ?? '' }}</td>
                    <td>{{ $movement->product?->category?->nombre ?? '' }}</td>
                    <td>{{ $movement->user?->name ?? 'Sin usuario' }}</td>
                    <td>{{ number_format($price, 2, '.', '') }}</td>
                    <td>{{ $movement->tipo === 'venta' ? number_format($amount, 2, '.', '') : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12">No hay movimientos para mostrar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
