@extends('layout')

@section('title', 'Ventas | Sistema de Inventario')
@section('page-title', 'Ventas')
@section('page-subtitle', 'Procesar ventas y descontar existencias')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Ventas de hoy</div>
                <div class="display-6 fw-bold text-success">{{ $stats['today_sales'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Productos disponibles</div>
                <div class="display-6 fw-bold">{{ $stats['available_products'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Bajo stock</div>
                <div class="display-6 fw-bold text-warning">{{ $stats['low_stock'] }}</div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <form method="GET" action="{{ route('inventory.sales.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label for="search" class="form-label fw-semibold">Buscar producto</label>
                            <input
                                id="search"
                                name="search"
                                value="{{ $search }}"
                                class="form-control"
                                placeholder="Nombre, SKU, codigo, marca o categoria"
                            >
                        </div>
                        <div class="col-md-3 d-grid">
                            <button class="btn btn-primary">Buscar</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Precio</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Estado</th>
                                <th style="width: 260px;">Procesar venta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $product) }}" class="fw-bold text-decoration-none">
                                            {{ $product->nombre }}
                                        </a>
                                        <div class="text-muted small">
                                            SKU: {{ $product->sku }}
                                            @if ($product->brand)
                                                <span class="ms-2">{{ $product->brand->nombre }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">C$ {{ number_format((float) $product->sale_price_1, 2) }}</td>
                                    <td class="text-center fw-bold">{{ $product->stock }}</td>
                                    <td class="text-center">
                                        @if ($product->isLowStock())
                                            <span class="badge text-bg-warning">Bajo stock</span>
                                        @else
                                            <span class="badge text-bg-success">Disponible</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button
                                            type="button"
                                            class="btn btn-success btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#saleModal"
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->nombre }}"
                                            data-product-sku="{{ $product->sku }}"
                                            data-product-price="{{ (float) $product->sale_price_1 }}"
                                            data-product-stock="{{ $product->stock }}"
                                        >
                                            Vender
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center text-muted">
                                        No hay productos disponibles para vender.
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
        </div>

        <div class="col-xl-4">
            <div class="table-card h-100">
                <div class="border-bottom p-3 p-md-4">
                    <h2 class="h5 mb-1">Ventas recientes</h2>
                    <div class="text-muted small">Ultimas salidas por venta.</div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($recentMovements as $movement)
                        <div class="list-group-item px-3 px-md-4 py-3">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-bold">{{ $movement->product?->nombre ?? 'Producto eliminado' }}</div>
                                    <div class="text-muted small">
                                        {{ $movement->user?->name ?? 'Sin usuario' }} - {{ $movement->created_at->format('d/m/Y h:i A') }}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge text-bg-success">-{{ $movement->cantidad }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            Todavia no hay ventas registradas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="saleModal" tabindex="-1" aria-label="Facturar venta" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('inventory.sales.process') }}" id="saleForm">
                    @csrf
                    <input type="hidden" name="product_id" id="saleProductId">

                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h2 class="h5 mb-1">Facturar venta</h2>
                            <div class="text-muted small" id="saleProductMeta">Seleccione un producto</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Producto</label>
                                <input id="saleProductName" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Precio unitario</label>
                                <div class="input-group">
                                    <span class="input-group-text">C$</span>
                                    <input id="saleUnitPrice" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="saleQuantity" class="form-label fw-semibold">Cantidad *</label>
                                <input id="saleQuantity" name="cantidad" type="number" min="1" class="form-control" required>
                                <div class="form-text" id="saleStockText"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="saleReceipt" class="form-label fw-semibold">Comprobante</label>
                                <select id="saleReceipt" name="comprobante" class="form-select" required>
                                    <option value="ticket">Ticket</option>
                                    <option value="factura">Factura</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="salePaymentMethod" class="form-label fw-semibold">Metodo de pago</label>
                                <select id="salePaymentMethod" name="metodo_pago" class="form-select" required>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">A pagar *</label>
                                <div class="input-group">
                                    <span class="input-group-text">C$</span>
                                    <input id="saleTotal" class="form-control" readonly value="0.00">
                                </div>
                            </div>
                            <div class="col-md-4" id="cashReceivedGroup">
                                <label for="saleCashReceived" class="form-label fw-semibold">Efectivo recibido *</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" data-cash-step="-10">-</button>
                                    <input id="saleCashReceived" name="efectivo_recibido" type="number" min="0" step="0.01" class="form-control text-end" value="0.00">
                                    <button class="btn btn-outline-secondary" type="button" data-cash-step="10">+</button>
                                </div>
                            </div>
                            <div class="col-md-4" id="saleChangeGroup">
                                <label class="form-label fw-semibold">Cambio *</label>
                                <div class="input-group">
                                    <span class="input-group-text">C$</span>
                                    <input id="saleChange" class="form-control" readonly value="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-success">Facturar venta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('saleModal');
            const form = document.getElementById('saleForm');
            const productId = document.getElementById('saleProductId');
            const productName = document.getElementById('saleProductName');
            const productMeta = document.getElementById('saleProductMeta');
            const unitPrice = document.getElementById('saleUnitPrice');
            const quantity = document.getElementById('saleQuantity');
            const stockText = document.getElementById('saleStockText');
            const paymentMethod = document.getElementById('salePaymentMethod');
            const total = document.getElementById('saleTotal');
            const cashReceived = document.getElementById('saleCashReceived');
            const change = document.getElementById('saleChange');
            const cashReceivedGroup = document.getElementById('cashReceivedGroup');
            const changeGroup = document.getElementById('saleChangeGroup');

            const money = (value) => Number(value || 0).toFixed(2);

            const recalculate = () => {
                const amount = Number(unitPrice.dataset.value || 0) * Number(quantity.value || 0);
                const isCash = paymentMethod.value === 'efectivo';

                total.value = money(amount);
                cashReceivedGroup.classList.toggle('d-none', !isCash);
                changeGroup.classList.toggle('d-none', !isCash);
                cashReceived.required = isCash;

                if (!isCash) {
                    cashReceived.value = money(amount);
                    change.value = '0.00';
                    return;
                }

                change.value = money(Math.max(Number(cashReceived.value || 0) - amount, 0));
            };

            modal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                const price = Number(button.getAttribute('data-product-price') || 0);
                const stock = Number(button.getAttribute('data-product-stock') || 0);

                form.reset();
                productId.value = button.getAttribute('data-product-id');
                productName.value = button.getAttribute('data-product-name');
                productMeta.textContent = `${button.getAttribute('data-product-sku')} - Stock disponible: ${stock}`;
                unitPrice.dataset.value = price;
                unitPrice.value = money(price);
                quantity.max = stock;
                quantity.value = 1;
                stockText.textContent = `Disponible: ${stock}`;
                cashReceived.value = money(price);
                recalculate();
            });

            [quantity, paymentMethod, cashReceived].forEach((input) => {
                input.addEventListener('input', recalculate);
                input.addEventListener('change', recalculate);
            });

            modal.querySelectorAll('[data-cash-step]').forEach((button) => {
                button.addEventListener('click', () => {
                    cashReceived.value = money(Number(cashReceived.value || 0) + Number(button.dataset.cashStep));
                    recalculate();
                });
            });
        });
    </script>
@endsection
