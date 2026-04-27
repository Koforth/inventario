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

    <div class="col-md-6">
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

    <div class="col-md-6">
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
        <label for="precio" class="form-label fw-semibold">Precio *</label>
        <input id="precio" type="number" min="0" step="0.01" name="precio" value="{{ old('precio', $product->precio ?? 0) }}" required class="form-control">
        @error('precio') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label for="proveedor" class="form-label fw-semibold">Proveedor</label>
        <input id="proveedor" name="proveedor" value="{{ old('proveedor', $product->proveedor) }}" maxlength="160" class="form-control">
        @error('proveedor') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label for="descripcion" class="form-label fw-semibold">Descripcion</label>
        <textarea id="descripcion" name="descripcion" rows="4" maxlength="1000" class="form-control">{{ old('descripcion', $product->descripcion) }}</textarea>
        @error('descripcion') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label for="image" class="form-label fw-semibold">Imagen del producto JPG/JPEG</label>
        <input id="image" type="file" name="image" accept=".jpg,.jpeg,image/jpeg" class="form-control">
        <div class="form-text">Sube una imagen sencilla para presentar el producto. Maximo 2 MB.</div>
        @error('image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

        @if ($product->image_path)
            <div class="mt-3">
                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->nombre }}" class="rounded border" style="width: 120px; height: 90px; object-fit: cover;">
            </div>
        @endif
    </div>
</div>

<div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-4">
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    <button class="btn btn-primary">Guardar producto</button>
</div>
