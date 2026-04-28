@extends('layout')

@section('title', 'Generar cotizacion | Sistema de Inventario')
@section('page-title', 'Generar cotizacion')
@section('page-subtitle', 'Selecciona productos y calcula el total')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('quotes.store') }}" id="quoteForm">
        @csrf
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="table-card p-3 p-md-4">
                    <h2 class="h5 mb-3">Cliente</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="customer_name" class="form-label fw-semibold">Nombre *</label>
                            <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_phone" class="form-label fw-semibold">Telefono</label>
                            <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="customer_email" class="form-label fw-semibold">Correo</label>
                            <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email') }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label fw-semibold">Notas</label>
                            <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="table-card mt-4">
                    <div class="border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <h2 class="h5 mb-1">Productos</h2>
                            <div class="text-muted small">Agrega los productos de la cotizacion.</div>
                        </div>
                        <button type="button" class="btn btn-outline-primary" id="addQuoteItem">Agregar</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th style="width: 130px;">Cantidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Total</th>
                                    <th style="width: 70px;"></th>
                                </tr>
                            </thead>
                            <tbody id="quoteItems"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="table-card p-3 p-md-4 position-sticky" style="top: 1rem;">
                    <h2 class="h5 mb-3">Resumen</h2>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <strong id="quoteSubtotal">C$ 0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Impuesto</span>
                        <strong id="quoteTax">C$ 0.00</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between h4">
                        <span>Total</span>
                        <strong id="quoteTotal">C$ 0.00</strong>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button class="btn btn-success">Guardar cotizacion</button>
                        <a href="{{ route('quotes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <template id="quoteItemTemplate">
        <tr>
            <td>
                <select class="form-select quote-product" required>
                    <option value="">Seleccionar producto</option>
                    @foreach ($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-price="{{ (float) $product->sale_price_1 }}"
                            data-taxable="{{ $product->taxable ? '1' : '0' }}"
                        >
                            {{ $product->nombre }} - {{ $product->sku }} - C$ {{ number_format((float) $product->sale_price_1, 2) }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" min="1" value="1" class="form-control quote-quantity" required>
            </td>
            <td class="text-end quote-price">C$ 0.00</td>
            <td class="text-end fw-bold quote-line-total">C$ 0.00</td>
            <td class="text-end">
                <button type="button" class="btn btn-outline-danger btn-sm quote-remove">Quitar</button>
            </td>
        </tr>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const items = document.getElementById('quoteItems');
            const template = document.getElementById('quoteItemTemplate');
            const addButton = document.getElementById('addQuoteItem');
            const subtotalLabel = document.getElementById('quoteSubtotal');
            const taxLabel = document.getElementById('quoteTax');
            const totalLabel = document.getElementById('quoteTotal');
            const taxRate = {{ $taxRate }};
            let rowIndex = 0;

            const money = (value) => `C$ ${Number(value || 0).toFixed(2)}`;

            const refreshNames = () => {
                [...items.querySelectorAll('tr')].forEach((row, index) => {
                    row.querySelector('.quote-product').name = `items[${index}][product_id]`;
                    row.querySelector('.quote-quantity').name = `items[${index}][quantity]`;
                });
            };

            const recalculate = () => {
                let subtotal = 0;
                let tax = 0;

                items.querySelectorAll('tr').forEach((row) => {
                    const option = row.querySelector('.quote-product').selectedOptions[0];
                    const price = Number(option?.dataset.price || 0);
                    const taxable = option?.dataset.taxable === '1';
                    const quantity = Number(row.querySelector('.quote-quantity').value || 0);
                    const lineSubtotal = price * quantity;
                    const lineTax = taxable ? lineSubtotal * taxRate : 0;

                    row.querySelector('.quote-price').textContent = money(price);
                    row.querySelector('.quote-line-total').textContent = money(lineSubtotal + lineTax);
                    subtotal += lineSubtotal;
                    tax += lineTax;
                });

                subtotalLabel.textContent = money(subtotal);
                taxLabel.textContent = money(tax);
                totalLabel.textContent = money(subtotal + tax);
            };

            const addRow = () => {
                const row = template.content.firstElementChild.cloneNode(true);
                row.dataset.index = rowIndex++;
                items.appendChild(row);
                refreshNames();
                recalculate();
            };

            addButton.addEventListener('click', addRow);
            items.addEventListener('input', recalculate);
            items.addEventListener('change', recalculate);
            items.addEventListener('click', (event) => {
                if (event.target.classList.contains('quote-remove')) {
                    event.target.closest('tr').remove();
                    refreshNames();
                    recalculate();
                }
            });

            addRow();
        });
    </script>
@endsection
