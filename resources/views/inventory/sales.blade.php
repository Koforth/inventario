@extends('layout')

@section('title', 'Ventas | Sistema de Inventario')
@section('page-title', 'Ventas')
@section('page-subtitle', 'Venta rapida por codigo o nombre con multiples precios y descuentos')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="metric-card p-4 h-100"><div class="text-muted small text-uppercase fw-semibold">Ventas hoy</div><div class="display-6 fw-bold">{{ $stats['today_sales'] }}</div></div></div>
        <div class="col-md-4"><div class="metric-card p-4 h-100"><div class="text-muted small text-uppercase fw-semibold">Monto vendido hoy</div><div class="display-6 fw-bold text-success">C$ {{ number_format($stats['today_amount'], 2) }}</div></div></div>
        <div class="col-md-4"><div class="metric-card p-4 h-100"><div class="text-muted small text-uppercase fw-semibold">Productos con stock</div><div class="display-6 fw-bold">{{ $stats['available_products'] }}</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <form method="GET" action="{{ route('inventory.sales.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label fw-semibold">Buscar producto</label>
                            <input name="search" value="{{ $search }}" class="form-control" placeholder="Codigo de barras, nombre, SKU o marca">
                        </div>
                        <div class="col-md-3 d-grid"><button class="btn btn-outline-primary">Buscar</button></div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Producto</th><th class="text-end">Precios</th><th class="text-center">Stock</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $product->nombre }}</div>
                                        <div class="text-muted small">{{ $product->barcode ?: $product->sku }}</div>
                                    </td>
                                    <td class="text-end small">
                                        P1 C$ {{ number_format((float) $product->sale_price_1, 2) }}<br>
                                        P2 C$ {{ number_format((float) ($product->sale_price_2 ?: $product->sale_price_1), 2) }}<br>
                                        P3 C$ {{ number_format((float) ($product->sale_price_3 ?: $product->sale_price_1), 2) }}
                                    </td>
                                    <td class="text-center fw-bold">{{ $product->stock }}</td>
                                    <td class="text-end">
                                        <button
                                            type="button"
                                            class="btn btn-success btn-sm js-add-item"
                                            data-id="{{ $product->id }}"
                                            data-name="{{ $product->nombre }}"
                                            data-stock="{{ $product->stock }}"
                                            data-p1="{{ (float) $product->sale_price_1 }}"
                                            data-p2="{{ (float) ($product->sale_price_2 ?: $product->sale_price_1) }}"
                                            data-p3="{{ (float) ($product->sale_price_3 ?: $product->sale_price_1) }}"
                                        >Agregar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-5 text-center text-muted">No hay productos para vender.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-top px-3 px-md-4 py-3">{{ $products->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="table-card p-3 p-md-4">
                <form method="POST" action="{{ route('inventory.sales.process') }}" id="saleForm">
                    @csrf
                    <h2 class="h5 mb-3">Realizar venta</h2>

                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label fw-semibold">Cliente</label>
                            <select name="customer_id" id="customerId" class="form-select">
                                <option value="">Consumidor final</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->typeLabel() }}: {{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4 d-grid align-items-end">
                            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary mt-auto">Clientes</a>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo venta</label>
                            <select name="sale_type" id="saleType" class="form-select" required>
                                <option value="contado">Contado</option>
                                <option value="credito">Credito</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pago</label>
                            <select name="payment_method" id="paymentMethod" class="form-select" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="mixto">Efectivo + Tarjeta</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Comprobante</label>
                            <select name="receipt_type_id" class="form-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($receiptTypes as $receiptType)
                                    <option value="{{ $receiptType->id }}">{{ $receiptType->name }}{{ $receiptType->prefix ? ' (' . $receiptType->prefix . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Monto efectivo</label>
                            <input type="number" min="0" step="0.01" class="form-control text-end" id="cashAmount" name="cash_amount" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Monto tarjeta</label>
                            <input type="number" min="0" step="0.01" class="form-control text-end" id="cardAmount" name="card_amount" value="0">
                        </div>
                    </div>

                    <hr>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="cartTable">
                            <thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Desc.</th><th class="text-end">Total</th><th></th></tr></thead>
                            <tbody><tr><td colspan="6" class="text-center text-muted py-3">Sin productos en la venta.</td></tr></tbody>
                        </table>
                    </div>

                    <input type="hidden" name="items" id="itemsInput">

                    <div class="d-grid gap-1 text-end">
                        <div>Subtotal: <b id="subtotalLabel">C$ 0.00</b></div>
                        <div>Descuento: <b id="discountLabel">C$ 0.00</b></div>
                        <div class="h5">Total: <span id="totalLabel">C$ 0.00</span></div>
                        <div class="small text-muted">Cambio: <span id="changeLabel">C$ 0.00</span></div>
                    </div>

                    <div class="d-grid mt-3">
                        <button class="btn btn-success">Registrar venta e imprimir comprobante</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="table-card mt-4">
        <div class="border-bottom p-3 p-md-4"><h2 class="h5 mb-1">Ventas recientes</h2></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Nro</th><th>Cliente</th><th>Tipo</th><th>Comprobante</th><th class="text-end">Total</th><th>Fecha</th></tr></thead>
                <tbody>
                    @forelse ($recentSales as $sale)
                        <tr>
                            <td>{{ $sale->number }}</td>
                            <td>{{ $sale->customer?->name ?: 'Consumidor final' }}</td>
                            <td>{{ ucfirst($sale->sale_type) }}</td>
                            <td>{{ $sale->receipt_number ?: ucfirst($sale->receipt_type) }}</td>
                            <td class="text-end">C$ {{ number_format((float) $sale->total, 2) }}</td>
                            <td>{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-muted">Aun no hay ventas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-card mt-4">
        <div class="border-bottom p-3 p-md-4">
            <h2 class="h5 mb-3">Consulta detallada de ventas</h2>
            <form method="GET" action="{{ route('inventory.sales.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="search" value="{{ $search }}">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tipo de consulta</label>
                    <select name="report_type" id="reportType" class="form-select">
                        <option value="day" @selected($reportType === 'day')>Dia actual</option>
                        <option value="range" @selected($reportType === 'range')>Rango de fechas</option>
                        <option value="month" @selected($reportType === 'month')>Mes especifico</option>
                    </select>
                </div>
                <div class="col-md-3" id="fromGroup">
                    <label class="form-label fw-semibold">Desde</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-3" id="toGroup">
                    <label class="form-label fw-semibold">Hasta</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-3" id="monthGroup">
                    <label class="form-label fw-semibold">Mes</label>
                    <input type="month" name="month" class="form-control" value="{{ $month }}">
                </div>
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Total de ventas: <b>{{ $salesTotals['count'] }}</b> | Monto: <b>C$ {{ number_format((float) $salesTotals['amount'], 2) }}</b>
                    </div>
                    <button class="btn btn-primary">Consultar</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nro</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Detalle</th>
                        <th>Pago</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($filteredSales as $sale)
                        <tr>
                            <td>{{ $sale->number }}</td>
                            <td>{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
                            <td>{{ $sale->customer?->name ?: 'Consumidor final' }}</td>
                            <td class="small">
                                @foreach ($sale->items as $item)
                                    <div>{{ $item->product?->nombre ?: 'Producto eliminado' }} (x{{ $item->quantity }}) - C$ {{ number_format((float) $item->line_total, 2) }}</div>
                                @endforeach
                            </td>
                            <td class="small">
                                <div>{{ ucfirst($sale->sale_type) }} / {{ ucfirst($sale->payment_method) }}</div>
                                <div class="text-muted">{{ $sale->receipt_number ?: ucfirst($sale->receipt_type) }}</div>
                            </td>
                            <td class="text-end fw-bold">C$ {{ number_format((float) $sale->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-muted">No hay ventas para el filtro seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-top px-3 px-md-4 py-3">
            {{ $filteredSales->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cart = [];
            const money = (v) => Number(v || 0).toFixed(2);
            const cartBody = document.querySelector('#cartTable tbody');
            const itemsInput = document.getElementById('itemsInput');
            const subtotalLabel = document.getElementById('subtotalLabel');
            const discountLabel = document.getElementById('discountLabel');
            const totalLabel = document.getElementById('totalLabel');
            const changeLabel = document.getElementById('changeLabel');
            const saleType = document.getElementById('saleType');
            const paymentMethod = document.getElementById('paymentMethod');
            const cashAmount = document.getElementById('cashAmount');
            const cardAmount = document.getElementById('cardAmount');
            const customerId = document.getElementById('customerId');
            const reportType = document.getElementById('reportType');
            const fromGroup = document.getElementById('fromGroup');
            const toGroup = document.getElementById('toGroup');
            const monthGroup = document.getElementById('monthGroup');

            const recalc = () => {
                let subtotal = 0;
                let discount = 0;
                let total = 0;

                cart.forEach((item) => {
                    const unitPrice = Number(item['p' + item.priceType]);
                    const lineSubtotal = unitPrice * item.quantity;
                    const lineDiscount = Math.min(lineSubtotal, item.discount);
                    item.lineTotal = lineSubtotal - lineDiscount;
                    subtotal += lineSubtotal;
                    discount += lineDiscount;
                    total += item.lineTotal;
                });

                subtotalLabel.textContent = 'C$ ' + money(subtotal);
                discountLabel.textContent = 'C$ ' + money(discount);
                totalLabel.textContent = 'C$ ' + money(total);
                const paid = Number(cashAmount.value || 0) + Number(cardAmount.value || 0);
                changeLabel.textContent = 'C$ ' + money(Math.max(paid - total, 0));
                itemsInput.value = JSON.stringify(cart.map(({ id, quantity, priceType, discount }) => ({ product_id: id, quantity, price_type: priceType, discount })));
            };

            const render = () => {
                if (!cart.length) {
                    cartBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Sin productos en la venta.</td></tr>';
                    recalc();
                    return;
                }

                cartBody.innerHTML = cart.map((item, index) => `
                    ${(() => {
                        const unitPrice = Number(item['p' + item.priceType] || 0);
                        const lineSubtotal = unitPrice * Number(item.quantity || 0);
                        const lineDiscount = Math.min(lineSubtotal, Number(item.discount || 0));
                        const previewTotal = lineSubtotal - lineDiscount;
                        return `
                    <tr>
                        <td>${item.name}</td>
                        <td><input type="number" min="1" max="${item.stock}" value="${item.quantity}" class="form-control form-control-sm text-end js-qty" data-index="${index}"></td>
                        <td>
                            <select class="form-select form-select-sm js-price" data-index="${index}">
                                <option value="1" ${item.priceType === 1 ? 'selected' : ''}>P1 C$ ${money(item.p1)}</option>
                                <option value="2" ${item.priceType === 2 ? 'selected' : ''}>P2 C$ ${money(item.p2)}</option>
                                <option value="3" ${item.priceType === 3 ? 'selected' : ''}>P3 C$ ${money(item.p3)}</option>
                            </select>
                        </td>
                        <td><input type="number" min="0" step="0.01" value="${money(item.discount)}" class="form-control form-control-sm text-end js-discount" data-index="${index}"></td>
                        <td class="text-end">C$ ${money(previewTotal)}</td>
                        <td class="text-end"><button type="button" class="btn btn-outline-danger btn-sm js-remove" data-index="${index}"><i class="bi bi-trash"></i></button></td>
                    </tr>
                        `;
                    })()}
                `).join('');

                recalc();
            };

            document.querySelectorAll('.js-add-item').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = Number(button.dataset.id);
                    const exists = cart.find((x) => x.id === id);
                    if (exists) {
                        exists.quantity = Math.min(exists.quantity + 1, exists.stock);
                    } else {
                        cart.push({ id, name: button.dataset.name, stock: Number(button.dataset.stock), quantity: 1, priceType: 1, discount: 0, p1: Number(button.dataset.p1), p2: Number(button.dataset.p2), p3: Number(button.dataset.p3) });
                    }
                    render();
                });
            });

            cartBody.addEventListener('input', (event) => {
                const el = event.target;
                const index = Number(el.dataset.index);
                if (Number.isNaN(index) || !cart[index]) return;

                if (el.classList.contains('js-qty')) cart[index].quantity = Math.max(1, Math.min(Number(el.value || 1), cart[index].stock));
                if (el.classList.contains('js-price')) cart[index].priceType = Number(el.value || 1);
                if (el.classList.contains('js-discount')) cart[index].discount = Math.max(0, Number(el.value || 0));
                render();
            });

            cartBody.addEventListener('click', (event) => {
                const button = event.target.closest('.js-remove');
                if (!button) return;
                cart.splice(Number(button.dataset.index), 1);
                render();
            });

            [saleType, paymentMethod, cashAmount, cardAmount].forEach((el) => {
                el.addEventListener('input', recalc);
                el.addEventListener('change', () => {
                    if (saleType.value === 'credito' && !customerId.value) {
                        alert('Para ventas al credito debes seleccionar un cliente.');
                    }
                    recalc();
                });
            });

            const toggleReportFields = () => {
                const type = reportType ? reportType.value : 'day';
                const isRange = type === 'range';
                const isMonth = type === 'month';
                if (fromGroup) fromGroup.classList.toggle('d-none', !isRange);
                if (toGroup) toGroup.classList.toggle('d-none', !isRange);
                if (monthGroup) monthGroup.classList.toggle('d-none', !isMonth);
            };

            if (reportType) {
                reportType.addEventListener('change', toggleReportFields);
                toggleReportFields();
            }

            document.getElementById('saleForm').addEventListener('submit', (event) => {
                if (!cart.length) {
                    event.preventDefault();
                    alert('Debes agregar al menos un producto.');
                    return;
                }

                if (saleType.value === 'credito' && !customerId.value) {
                    event.preventDefault();
                    alert('Debes seleccionar un cliente para venta al credito.');
                }
            });
        });
    </script>
@endsection
