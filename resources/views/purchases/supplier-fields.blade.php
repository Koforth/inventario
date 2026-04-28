<div class="col-md-6">
    <label class="form-label fw-semibold">Nombre *</label>
    <input name="name" value="{{ old('name', $supplier->name) }}" class="form-control" required>
</div>
<div class="col-md-3">
    <label class="form-label fw-semibold">DNI</label>
    <input name="dni" value="{{ old('dni', $supplier->dni) }}" class="form-control">
</div>
<div class="col-md-3">
    <label class="form-label fw-semibold">RUC</label>
    <input name="ruc" value="{{ old('ruc', $supplier->ruc) }}" class="form-control">
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Contacto</label>
    <input name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" class="form-control">
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Telefono</label>
    <input name="phone" value="{{ old('phone', $supplier->phone) }}" class="form-control">
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Correo</label>
    <input name="email" type="email" value="{{ old('email', $supplier->email) }}" class="form-control">
</div>
<div class="col-12">
    <label class="form-label fw-semibold">Direccion</label>
    <input name="address" value="{{ old('address', $supplier->address) }}" class="form-control">
</div>
