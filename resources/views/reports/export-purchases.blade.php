<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <thead>
            <tr><th colspan="9">Reporte de compras</th></tr>
            <tr>
                <th colspan="9">
                    Generado: {{ $generatedAt->format('d/m/Y h:i A') }}
                    | Desde: {{ $from->format('d/m/Y') }}
                    | Hasta: {{ $to->format('d/m/Y') }}
                </th>
            </tr>
            <tr>
                <th>Nro</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Usuario</th>
                <th>Tipo</th>
                <th>Pago</th>
                <th>Subtotal</th>
                <th>Impuestos</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->number }}</td>
                    <td>{{ $purchase->created_at->format('d/m/Y h:i A') }}</td>
                    <td>{{ $purchase->supplier?->name ?? 'Sin proveedor' }}</td>
                    <td>{{ $purchase->user?->name ?? '' }}</td>
                    <td>{{ $purchase->purchase_type }}</td>
                    <td>{{ $purchase->payment_method }}</td>
                    <td>{{ number_format($purchase->subtotal, 2, '.', '') }}</td>
                    <td>{{ number_format($purchase->items->sum('tax_amount'), 2, '.', '') }}</td>
                    <td>{{ number_format($purchase->total, 2, '.', '') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="8">TOTAL</td>
                <td>{{ number_format($purchases->sum('total'), 2, '.', '') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>