<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <thead>
            <tr><th colspan="11">Reporte de ventas</th></tr>
            <tr>
                <th colspan="11">
                    Generado: {{ $generatedAt->format('d/m/Y h:i A') }}
                    | Desde: {{ $from->format('d/m/Y') }}
                    | Hasta: {{ $to->format('d/m/Y') }}
                </th>
            </tr>
            <tr>
                <th>Nro</th>
                <th>Comprobante</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Usuario</th>
                <th>Tipo</th>
                <th>Pago</th>
                <th>Subtotal</th>
                <th>Descuento</th>
                <th>Total</th>
                <th>Credito pendiente</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
                <tr>
                    <td>{{ $sale->number }}</td>
                    <td>{{ strtoupper($sale->receipt_type) }} {{ $sale->receipt_number }}</td>
                    <td>{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
                    <td>{{ $sale->customer?->name ?? 'Consumidor final' }}</td>
                    <td>{{ $sale->user?->name ?? '' }}</td>
                    <td>{{ $sale->sale_type }}</td>
                    <td>{{ $sale->payment_method }}</td>
                    <td>{{ number_format($sale->subtotal, 2, '.', '') }}</td>
                    <td>{{ number_format($sale->discount_total, 2, '.', '') }}</td>
                    <td>{{ number_format($sale->total, 2, '.', '') }}</td>
                    <td>{{ number_format($sale->credit_balance, 2, '.', '') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="9">TOTAL</td>
                <td>{{ number_format($sales->sum('total'), 2, '.', '') }}</td>
                <td>{{ number_format($sales->sum('credit_balance'), 2, '.', '') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>