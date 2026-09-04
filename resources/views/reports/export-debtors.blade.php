<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <thead>
            <tr><th colspan="5">Reporte de clientes morosos</th></tr>
            <tr><th colspan="5">Generado: {{ $generatedAt->format('d/m/Y h:i A') }}</th></tr>
            <tr>
                <th>Cliente</th>
                <th>Identificacion</th>
                <th>Limite crediticio</th>
                <th>Saldo pendiente</th>
                <th>% de deuda</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($debtors as $item)
                <tr>
                    <td>{{ $item->customer->name }}</td>
                    <td>{{ $item->customer->dni ?: $item->customer->ruc ?: 'Sin identificacion' }}</td>
                    <td>{{ number_format($item->customer->credit_limit, 2, '.', '') }}</td>
                    <td>{{ number_format($item->balance, 2, '.', '') }}</td>
                    <td>{{ $item->customer->credit_limit > 0 ? round($item->balance / $item->customer->credit_limit * 100) : 100 }}%</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3">TOTAL PENDIENTE</td>
                <td>{{ number_format($debtors->sum('balance'), 2, '.', '') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>