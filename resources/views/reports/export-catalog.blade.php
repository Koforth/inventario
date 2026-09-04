<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <thead>
            <tr><th colspan="9">Catalogo de productos</th></tr>
            <tr><th colspan="9">Generado: {{ $generatedAt->format('d/m/Y h:i A') }}</th></tr>
            <tr>
                <th>SKU</th>
                <th>Codigo de barras</th>
                <th>Producto</th>
                <th>Categoria</th>
                <th>Marca</th>
                <th>Presentacion</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Proveedores</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->barcode }}</td>
                    <td>{{ $product->nombre }}</td>
                    <td>{{ $product->category?->nombre ?? '' }}</td>
                    <td>{{ $product->brand?->nombre ?? '' }}</td>
                    <td>{{ $product->presentation?->nombre ?? '' }}</td>
                    <td>{{ number_format($product->primaryPrice(), 2, '.', '') }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>{{ $product->supplierNames() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>