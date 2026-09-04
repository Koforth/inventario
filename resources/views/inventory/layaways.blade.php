@extends('layout')

@section('title', 'Apartados | SmartZone')
@section('page-title', 'Apartados')
@section('page-subtitle', 'Separar productos, consultar retiros y gestionar ventas al credito')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <form method="GET" action="{{ route('inventory.layaways.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label fw-semibold">Buscar producto</label>
                            <input name="search" value="{{ $search }}" class="form-control" placeholder="Codigo de barras, nombre o SKU">
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
                                    <td><div class="fw-bold">{{ $product->nombre }}</div><div class="text-muted small">{{ $product->barcode ?: $product->sku }}</div></td>
                                    <td class="text-end small">
                                        P1 C$ {{ number_format((float) $product->sale_price_1, 2) }}<br>
                                        P2 C$ {{ number_format((float) ($product->sale_price_2 ?: $product->sale_price_1), 2) }}<br>
                                        P3 C$ {{ number_format((float) ($product->sale_price_3 ?: $product->sale_price_1), 2) }}
                                    </td>
                                    <td class="text-center fw-bold">{{ $product->stock }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-success btn-sm js-add-item"
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
                                <tr><td colspan="4" class="py-5 text-center text-muted">No hay productos disponibles.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-top px-3 px-md-4 py-3">{{ $products->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="table-card p-3 p-md-4">
                <form method="POST" action="{{ route('inventory.layaways.store') }}" id="layawayForm">
                    @csrf
                    <h2 class="h5 mb-3">Apartar productos</h2>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Cliente *</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">Seleccione cliente</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->typeLabel() }}: {{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha y hora retiro *</label>
                            <input type="datetime-local" name="pickup_at" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Monto inicial *</label>
                            <input type="number" min="0" step="0.01" name="initial_payment" id="initialPayment" class="form-control text-end" value="0">
                        </div>
                    </div>

                    <hr>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="cartTable">
                            <thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Desc.</th><th class="text-end">Total</th><th></th></tr></thead>
                            <tbody><tr><td colspan="6" class="text-center text-muted py-3">Sin productos en el apartado.</td></tr></tbody>
                        </table>
                    </div>
                    <input type="hidden" name="items" id="itemsInput">

                    <div class="d-grid gap-1 text-end">
                        <div>Subtotal: <b id="subtotalLabel">C$ 0.00</b></div>
                        <div>Descuento: <b id="discountLabel">C$ 0.00</b></div>
                        <div>Total: <b id="totalLabel">C$ 0.00</b></div>
                        <div>Saldo: <b id="balanceLabel">C$ 0.00</b></div>
                    </div>

                    <div class="d-grid mt-3">
                        <button class="btn btn-primary">Guardar apartado e imprimir comprobante</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="table-card mt-4">
        <div class="border-bottom p-3 p-md-4">
            <h2 class="h5 mb-3">Consultar apartados</h2>
            <form method="GET" action="{{ route('inventory.layaways.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="search" value="{{ $search }}">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Consulta</label>
                    <select name="report_type" id="reportType" class="form-select">
                        <option value="day" @selected($reportType === 'day')>Dia actual</option>
                        <option value="range" @selected($reportType === 'range')>Rango fechas</option>
                        <option value="month" @selected($reportType === 'month')>Mes especifico</option>
                    </select>
                </div>
                <div class="col-md-3" id="fromGroup"><label class="form-label fw-semibold">Desde</label><input type="date" name="from" class="form-control" value="{{ $from }}"></div>
                <div class="col-md-3" id="toGroup"><label class="form-label fw-semibold">Hasta</label><input type="date" name="to" class="form-control" value="{{ $to }}"></div>
                <div class="col-md-3" id="monthGroup"><label class="form-label fw-semibold">Mes</label><input type="month" name="month" class="form-control" value="{{ $month }}"></div>
                <div class="col-12 text-end"><button class="btn btn-primary">Consultar</button></div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Apartado</th><th>Cliente</th><th>Retiro</th><th>Detalle</th><th class="text-end">Total / Saldo</th><th style="width: 260px;">Acciones</th></tr></thead>
                <tbody>
                    @forelse ($layaways as $layaway)
                        <tr>
                            <td>{{ $layaway->number }}</td>
                            <td>{{ $layaway->customer?->name }}</td>
                            <td>{{ $layaway->pickup_at->format('d/m/Y h:i A') }}</td>
                            <td class="small">@foreach($layaway->items as $item)<div>{{ $item->product?->nombre }} (x{{ $item->quantity }})</div>@endforeach</td>
                            <td class="text-end">C$ {{ number_format((float) $layaway->total, 2) }}<div class="small text-danger">Saldo: C$ {{ number_format((float) $layaway->balance, 2) }}</div></td>
                            <td>
                                <form method="POST" action="{{ route('inventory.layaways.pay', $layaway) }}" class="d-flex gap-2 mb-2">
                                    @csrf
                                    <input name="amount" type="number" min="0.01" max="{{ $layaway->balance }}" step="0.01" class="form-control form-control-sm" placeholder="Abono">
                                    <button class="btn btn-outline-primary btn-sm">Abonar</button>
                                </form>
                                <form method="POST" action="{{ route('inventory.layaways.complete', $layaway) }}" class="d-grid">
                                    @csrf
                                    <button class="btn btn-success btn-sm" @disabled((float)$layaway->balance > 0)>Finalizar venta</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-muted">No hay apartados para ese filtro.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-top px-3 px-md-4 py-3">{{ $layaways->links('pagination::bootstrap-5') }}</div>
    </div>

    <div class="table-card mt-4">
        <div class="border-bottom p-3 p-md-4"><h2 class="h5 mb-1">Ventas al credito</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Venta</th><th>Cliente</th><th>Fecha</th><th class="text-end">Total</th><th class="text-end">Saldo</th><th style="width:220px;">Abono</th></tr></thead>
                <tbody>
                    @forelse ($creditSales as $sale)
                        <tr>
                            <td>{{ $sale->number }}</td>
                            <td>{{ $sale->customer?->name ?: 'Consumidor final' }}</td>
                            <td>{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
                            <td class="text-end">C$ {{ number_format((float) $sale->total, 2) }}</td>
                            <td class="text-end text-danger fw-bold">C$ {{ number_format((float) $sale->credit_balance, 2) }}</td>
                            <td>
                                <form method="POST" action="{{ route('inventory.credit-sales.pay', $sale) }}" class="d-flex gap-2">
                                    @csrf
                                    <input name="amount" type="number" min="0.01" max="{{ $sale->credit_balance }}" step="0.01" class="form-control form-control-sm" placeholder="Abono">
                                    <button class="btn btn-outline-success btn-sm">Abonar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-muted">No hay ventas a credito pendientes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-top px-3 px-md-4 py-3">{{ $creditSales->links('pagination::bootstrap-5') }}</div>
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
            const balanceLabel = document.getElementById('balanceLabel');
            const initialPayment = document.getElementById('initialPayment');
            const reportType = document.getElementById('reportType');
            const fromGroup = document.getElementById('fromGroup');
            const toGroup = document.getElementById('toGroup');
            const monthGroup = document.getElementById('monthGroup');

            const recalc = () => {
                let subtotal = 0;
                let discount = 0;
                let total = 0;
                cart.forEach((item) => {
                    const unitPrice = Number(item['p' + item.priceType] || 0);
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
                balanceLabel.textContent = 'C$ ' + money(Math.max(total - Number(initialPayment.value || 0), 0));
                itemsInput.value = JSON.stringify(cart.map(({ id, quantity, priceType, discount }) => ({ product_id: id, quantity, price_type: priceType, discount })));
            };

            const render = () => {
                if (!cart.length) {
                    cartBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Sin productos en el apartado.</td></tr>';
                    recalc();
                    return;
                }
                cartBody.innerHTML = cart.map((item, index) => {
                    const unitPrice = Number(item['p' + item.priceType] || 0);
                    const lineSubtotal = unitPrice * Number(item.quantity || 0);
                    const lineDiscount = Math.min(lineSubtotal, Number(item.discount || 0));
                    const previewTotal = lineSubtotal - lineDiscount;
                    return `
                    <tr>
                        <td>${item.name}</td>
                        <td><input type="number" min="1" max="${item.stock}" value="${item.quantity}" class="form-control form-control-sm text-end js-qty" data-index="${index}"></td>
                        <td><select class="form-select form-select-sm js-price" data-index="${index}">
                            <option value="1" ${item.priceType === 1 ? 'selected' : ''}>P1 C$ ${money(item.p1)}</option>
                            <option value="2" ${item.priceType === 2 ? 'selected' : ''}>P2 C$ ${money(item.p2)}</option>
                            <option value="3" ${item.priceType === 3 ? 'selected' : ''}>P3 C$ ${money(item.p3)}</option>
                        </select></td>
                        <td><input type="number" min="0" step="0.01" value="${money(item.discount)}" class="form-control form-control-sm text-end js-discount" data-index="${index}"></td>
                        <td class="text-end">C$ ${money(previewTotal)}</td>
                        <td class="text-end"><button type="button" class="btn btn-outline-danger btn-sm js-remove" data-index="${index}"><i class="bi bi-trash"></i></button></td>
                    </tr>`;
                }).join('');
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

            initialPayment.addEventListener('input', recalc);

            const toggleReportFields = () => {
                const type = reportType.value;
                fromGroup.classList.toggle('d-none', type !== 'range');
                toGroup.classList.toggle('d-none', type !== 'range');
                monthGroup.classList.toggle('d-none', type !== 'month');
            };
            reportType.addEventListener('change', toggleReportFields);
            toggleReportFields();

            document.getElementById('layawayForm').addEventListener('submit', (event) => {
                if (!cart.length) {
                    event.preventDefault();
                    alert('Debes agregar productos al apartado.');
                }
            });
        });
    </script>
@endsection

