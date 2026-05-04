@csrf

<div class="row g-3">
    <div class="col-md-4">
        <label for="sku" class="form-label fw-semibold">SKU *</label>
        <input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required maxlength="50" class="form-control" data-sku-input>
        @error('sku') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="barcode" class="form-label fw-semibold">Codigo de barras</label>
        <input id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}" maxlength="100" class="form-control">
        @error('barcode') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="nombre" class="form-label fw-semibold">Nombre *</label>
        <input id="nombre" name="nombre" value="{{ old('nombre', $product->nombre) }}" required maxlength="255" class="form-control">
        @error('nombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="category_id" class="form-label fw-semibold">Categoria *</label>
        <select id="category_id" name="category_id" required class="form-select">
            <option value="">Seleccionar categoria</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>
                    {{ $category->nombre }}
                </option>
            @endforeach
        </select>
        @error('category_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="brand_id" class="form-label fw-semibold">Marca *</label>
        <select id="brand_id" name="brand_id" required class="form-select">
            <option value="">Seleccionar marca</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}" @selected((int) old('brand_id', $product->brand_id) === $brand->id)>
                    {{ $brand->nombre }}
                </option>
            @endforeach
        </select>
        @error('brand_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="presentation_id" class="form-label fw-semibold">Presentacion *</label>
        <select id="presentation_id" name="presentation_id" required class="form-select">
            <option value="">Seleccionar presentacion</option>
            @foreach ($presentations as $presentation)
                <option value="{{ $presentation->id }}" @selected((int) old('presentation_id', $product->presentation_id) === $presentation->id)>
                    {{ $presentation->nombre }}
                </option>
            @endforeach
        </select>
        @error('presentation_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="stock" class="form-label fw-semibold">Stock *</label>
        <input id="stock" type="number" min="0" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required class="form-control">
        @error('stock') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="stock_minimo" class="form-label fw-semibold">Stock minimo *</label>
        <input id="stock_minimo" type="number" min="0" name="stock_minimo" value="{{ old('stock_minimo', $product->stock_minimo ?? 0) }}" required class="form-control">
        @error('stock_minimo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="purchase_price" class="form-label fw-semibold">Precio de compra *</label>
        <input id="purchase_price" type="number" min="0" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? 0) }}" required class="form-control">
        @error('purchase_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        @php
            $oldPrices = old('prices');
            $priceRows = $oldPrices
                ? collect($oldPrices)
                : ($product->relationLoaded('prices') && $product->prices->where('is_active', true)->isNotEmpty()
                    ? $product->prices->where('is_active', true)->values()->map(fn ($price) => [
                        'id' => $price->id,
                        'label' => $price->label,
                        'amount' => $price->amount,
                    ])
                    : collect([
                        ['id' => null, 'label' => 'Precio 1', 'amount' => $product->sale_price_1 ?? $product->precio ?? 0],
                        ['id' => null, 'label' => 'Precio 2', 'amount' => $product->sale_price_2],
                        ['id' => null, 'label' => 'Precio 3', 'amount' => $product->sale_price_3],
                    ])->filter(fn ($price, $index) => $index === 0 || filled($price['amount'])));
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-semibold mb-0">Precios de venta *</label>
            <button type="button" class="btn btn-outline-primary btn-sm" id="addPriceRow">Agregar precio</button>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-1" id="pricesTable">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th style="width: 180px;">Monto</th>
                        <th style="width: 70px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($priceRows as $index => $price)
                        <tr>
                            <td>
                                <input type="hidden" name="prices[{{ $index }}][id]" value="{{ $price['id'] ?? '' }}">
                                <input name="prices[{{ $index }}][label]" value="{{ $price['label'] ?? '' }}" maxlength="80" required class="form-control form-control-sm" placeholder="Ejemplo: Mayorista">
                            </td>
                            <td>
                                <input type="number" min="0" step="0.01" name="prices[{{ $index }}][amount]" value="{{ $price['amount'] ?? 0 }}" required class="form-control form-control-sm text-end">
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-danger btn-sm js-remove-price">Quitar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="form-text">El primer precio se usara como precio principal en catalogo y reportes.</div>
        @error('prices') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        @error('prices.*.label') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        @error('prices.*.amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8">
        <div class="row g-2 h-100 align-items-end">
            <div class="col-sm-4">
                <div class="form-check form-switch border rounded-3 p-3 ps-5">
                    <input class="form-check-input" type="checkbox" id="taxable" name="taxable" value="1" @checked(old('taxable', $product->taxable))>
                    <label class="form-check-label fw-semibold" for="taxable">Sujeto a impuesto</label>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-check form-switch border rounded-3 p-3 ps-5">
                    <input class="form-check-input" type="checkbox" id="perishable" name="perishable" value="1" @checked(old('perishable', $product->perishable))>
                    <label class="form-check-label fw-semibold" for="perishable">Perecedero</label>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-check form-switch border rounded-3 p-3 ps-5">
                    <input class="form-check-input" type="checkbox" id="inventoryable" name="inventoryable" value="1" @checked(old('inventoryable', $product->inventoryable ?? true))>
                    <label class="form-check-label fw-semibold" for="inventoryable">Inventariable</label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <label for="expires_at" class="form-label fw-semibold">Fecha de vencimiento</label>
        <input id="expires_at" type="date" name="expires_at" value="{{ old('expires_at', optional($product->expires_at)->format('Y-m-d')) }}" class="form-control">
        @error('expires_at') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label for="proveedor" class="form-label fw-semibold">Proveedores</label>
        <input
            id="proveedor"
            name="proveedor"
            value="{{ old('proveedor', $product->exists ? $product->supplierNames() : $product->proveedor) }}"
            maxlength="160"
            class="form-control"
            placeholder="Ejemplo: Dulce Hogar, CasaPlus, HomeCare"
        >
        <div class="form-text">Separa varios proveedores con comas.</div>
        @error('proveedor') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label for="descripcion" class="form-label fw-semibold">Descripcion</label>
        <textarea id="descripcion" name="descripcion" rows="4" maxlength="1000" class="form-control">{{ old('descripcion', $product->descripcion) }}</textarea>
        @error('descripcion') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label for="images" class="form-label fw-semibold">Imagenes del producto JPG/JPEG</label>
        <input id="images" type="file" name="images[]" accept=".jpg,.jpeg,image/jpeg" multiple class="form-control">
        <div class="form-text">Puedes subir hasta 8 imagenes. Cada archivo debe pesar maximo 2 MB.</div>
        @error('images') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        @error('images.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

        @php
            $productImages = $product->exists ? $product->images : collect();
        @endphp

        @if ($productImages->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mt-3">
                @foreach ($productImages as $image)
                    <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $product->nombre }}" class="rounded border" style="width: 120px; height: 90px; object-fit: cover;">
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-4">
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    <button class="btn btn-primary">Guardar producto</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const table = document.querySelector('#pricesTable tbody');
        const addButton = document.getElementById('addPriceRow');

        const refreshNames = () => {
            table.querySelectorAll('tr').forEach((row, index) => {
                row.querySelectorAll('input').forEach((input) => {
                    input.name = input.name.replace(/prices\[\d+]/, `prices[${index}]`);
                });
            });
        };

        const ensureOneRow = () => {
            const removeButtons = table.querySelectorAll('.js-remove-price');
            removeButtons.forEach((button) => {
                button.disabled = removeButtons.length === 1;
            });
        };

        addButton.addEventListener('click', () => {
            const index = table.querySelectorAll('tr').length;
            table.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>
                        <input type="hidden" name="prices[${index}][id]" value="">
                        <input name="prices[${index}][label]" maxlength="80" required class="form-control form-control-sm" placeholder="Ejemplo: Mayorista">
                    </td>
                    <td>
                        <input type="number" min="0" step="0.01" name="prices[${index}][amount]" value="0" required class="form-control form-control-sm text-end">
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm js-remove-price">Quitar</button>
                    </td>
                </tr>
            `);
            ensureOneRow();
        });

        table.addEventListener('click', (event) => {
            const button = event.target.closest('.js-remove-price');
            if (!button || table.querySelectorAll('tr').length === 1) {
                return;
            }

            button.closest('tr').remove();
            refreshNames();
            ensureOneRow();
        });

        ensureOneRow();
    });
</script>
