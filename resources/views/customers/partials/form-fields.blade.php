<div class="col-12">
    <label class="form-label fw-semibold">Tipo *</label>
    <select name="customer_type" class="form-select" required>
        <option value="cliente" @selected(old('customer_type', $customer?->customer_type ?? 'cliente') === 'cliente')>Cliente</option>
        <option value="empresa" @selected(old('customer_type', $customer?->customer_type) === 'empresa')>Empresa</option>
    </select>
</div>
<div class="col-12">
    <label class="form-label fw-semibold">Nombre de cliente o empresa *</label>
    <input name="name" class="form-control" value="{{ old('name', $customer?->name) }}" required>
</div>
<div class="col-md-6">
    <label class="form-label fw-semibold">DNI</label>
    <input name="dni" class="form-control" value="{{ old('dni', $customer?->dni) }}">
</div>
<div class="col-md-6">
    <label class="form-label fw-semibold">RUC</label>
    <input name="ruc" class="form-control" value="{{ old('ruc', $customer?->ruc) }}">
</div>
<div class="col-md-6">
    <label class="form-label fw-semibold">Telefono</label>
    <input name="phone" class="form-control" value="{{ old('phone', $customer?->phone) }}">
</div>
<div class="col-md-6">
    <label class="form-label fw-semibold">Email</label>
    <input name="email" type="email" class="form-control" value="{{ old('email', $customer?->email) }}">
</div>
<div class="col-12">
    <label class="form-label fw-semibold">Giro de negocio</label>
    <input name="business_line" class="form-control" value="{{ old('business_line', $customer?->business_line) }}">
</div>
<div class="col-md-6">
    <label class="form-label fw-semibold">Limite crediticio</label>
    <input name="credit_limit" type="number" min="0" step="0.01" class="form-control" value="{{ old('credit_limit', $customer?->credit_limit ?? 0) }}">
</div>
<div class="col-md-6 d-flex align-items-end">
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', $customer?->is_active ?? true))>
        <label class="form-check-label fw-semibold" for="is_active">Cliente vigente</label>
    </div>
</div>
<div class="col-12">
    <label class="form-label fw-semibold">Direccion</label>
    <input name="address" class="form-control" value="{{ old('address', $customer?->address) }}">
</div>
