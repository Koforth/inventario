<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante {{ $sale->number }} | SmartZone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; color: #000; background: #f5f5f5; }
        .receipt { width: 80mm; max-width: 100%; margin: 20px auto; background: #fff; padding: 12px 10px; border: 1px solid #ddd; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px; }
        .header h1 { font-size: 16px; letter-spacing: 1px; }
        .header .co { color: #2563eb; font-size: 13px; }
        .meta { border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px; }
        .meta table { width: 100%; }
        .meta td { padding: 2px 0; }
        .meta .lbl { color: #555; width: 35%; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.items th { border-bottom: 1px solid #000; text-align: left; padding: 3px 2px; font-size: 11px; }
        table.items td { padding: 3px 2px; vertical-align: top; }
        .qty { text-align: center; }
        .right { text-align: right; }
        .totals { border-top: 1px dashed #000; padding-top: 8px; }
        .totals table { width: 100%; }
        .totals td { padding: 2px 0; }
        .totals .grand { font-size: 15px; font-weight: 700; }
        .totals .grand td { border-top: 2px solid #000; padding-top: 6px; }
        .payment { border-top: 1px dashed #000; padding-top: 8px; margin-top: 8px; }
        .footer { text-align: center; border-top: 1px dashed #000; padding-top: 8px; margin-top: 8px; font-size: 11px; }
        .actions { width: 80mm; margin: 12px auto; text-align: center; }
        .actions button { font-family: Arial, sans-serif; font-size: 14px; padding: 10px 28px; background: #2563eb; color: #fff; border: 0; border-radius: 6px; cursor: pointer; }
        .actions button:hover { background: #1d4ed8; }
        @media print {
            body { background: #fff; }
            .receipt { margin: 0 auto; border: 0; box-shadow: none; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">Imprimir comprobante</button>
    </div>

    <div class="receipt">
        <div class="header">
            <h1>SMARTZONE</h1>
            <div class="co">Tienda de celulares</div>
            <div>Direccion: Managua, Nicaragua</div>
            <div>Tel: +505 0000-0000</div>
        </div>

        <div class="meta">
            <table>
                <tr>
                    <td class="lbl">{{ strtoupper($sale->receipt_type) }} Nro:</td>
                    <td><b>{{ $sale->receipt_number ?: $sale->number }}</b></td>
                </tr>
                <tr>
                    <td class="lbl">Fecha:</td>
                    <td>{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
                </tr>
                <tr>
                    <td class="lbl">Cajero:</td>
                    <td>{{ $sale->user?->name ?? '-' }}</td>
                </tr>
                @if ($sale->customer)
                    <tr>
                        <td class="lbl">Cliente:</td>
                        <td>{{ $sale->customer->name }}</td>
                    </tr>
                    @if ($sale->customer->dni || $sale->customer->ruc)
                        <tr>
                            <td class="lbl">Identif.:</td>
                            <td>{{ $sale->customer->dni ?: $sale->customer->ruc }}</td>
                        </tr>
                    @endif
                @endif
                @if ($sale->customer?->customer_type === 'empresa' && $sale->customer?->company_name)
                    <tr>
                        <td class="lbl">Empresa:</td>
                        <td>{{ $sale->customer->company_name }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Cant</th>
                    <th>Descripcion</th>
                    <th class="right">P. Unit</th>
                    <th class="right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td class="qty">{{ $item->quantity }}</td>
                        <td>
                            {{ $item->product?->nombre ?? 'Producto eliminado' }}
                            @if ((float) $item->discount > 0)
                                <br><span style="color:#555">Desc: C$ {{ number_format((float) $item->discount, 2) }}</span>
                            @endif
                        </td>
                        <td class="right">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="right">{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td class="right">C$ {{ number_format((float) $sale->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Descuento</td>
                    <td class="right">C$ {{ number_format((float) $sale->discount_total, 2) }}</td>
                </tr>
                <tr class="grand">
                    <td>TOTAL</td>
                    <td class="right">C$ {{ number_format((float) $sale->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="payment">
            <table>
                <tr>
                    <td>Forma de pago</td>
                    <td class="right">{{ ucfirst($sale->payment_method) }} / {{ ucfirst($sale->sale_type) }}</td>
                </tr>
                @if ((float) $sale->cash_amount > 0)
                    <tr>
                        <td>Efectivo</td>
                        <td class="right">C$ {{ number_format((float) $sale->cash_amount, 2) }}</td>
                    </tr>
                @endif
                @if ((float) $sale->card_amount > 0)
                    <tr>
                        <td>Tarjeta</td>
                        <td class="right">C$ {{ number_format((float) $sale->card_amount, 2) }}</td>
                    </tr>
                @endif
                @if ((float) $sale->change_amount > 0)
                    <tr>
                        <td>Cambio</td>
                        <td class="right">C$ {{ number_format((float) $sale->change_amount, 2) }}</td>
                    </tr>
                @endif
                @if ((float) $sale->credit_balance > 0)
                    <tr>
                        <td>Saldo al credito</td>
                        <td class="right">C$ {{ number_format((float) $sale->credit_balance, 2) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="footer">
            Muchas gracias por su compra<br>
            SmartZone - Control de inventario y facturacion
        </div>
    </div>
</body>
</html>