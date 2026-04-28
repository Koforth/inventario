@extends('layout')

@section('title', 'Realizar compra | Compras')
@section('page-title', 'Realizar compra')
@section('page-subtitle', 'Busca productos por nombre o codigo de barras')

@section('content')
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    <form method="POST" action="{{ route('purchases.store') }}" id="purchaseForm">
        @csrf
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="table-card p-3 p-md-4 mb-4">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Proveedor *</label><select name="supplier_id" class="form-select" required><option value="">Seleccionar</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label fw-semibold">Condicion</label><select name="payment_type" id="paymentType" class="form-select"><option value="contado">Contado</option><option value="credito">Credito</option></select></div>
                        <div class="col-md-3"><label class="form-label fw-semibold">Abono inicial</label><input name="paid_amount" id="paidAmount" type="number" min="0" step="0.01" value="0" class="form-control"></div>
                        <div class="col-12"><label class="form-label fw-semibold">Notas</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
                    </div>
                </div>
                <div class="table-card">
                    <div class="border-bottom p-3 p-md-4 d-flex justify-content-between"><h2 class="h5 mb-0">Productos</h2><button type="button" id="addPurchaseItem" class="btn btn-outline-primary">Agregar</button></div>
                    <div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th>Producto</th><th>Cantidad</th><th>Costo</th><th class="text-end">Total</th><th></th></tr></thead><tbody id="purchaseItems"></tbody></table></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="table-card p-3 p-md-4 position-sticky" style="top:1rem">
                    <h2 class="h5 mb-3">Totales</h2>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong id="purchaseSubtotal">C$ 0.00</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Impuesto</span><strong id="purchaseTax">C$ 0.00</strong></div>
                    <hr><div class="d-flex justify-content-between h4"><span>Total</span><strong id="purchaseTotal">C$ 0.00</strong></div>
                    <div class="d-grid gap-2 mt-4"><button class="btn btn-success">Guardar compra</button><a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Cancelar</a></div>
                </div>
            </div>
        </div>
    </form>
    <template id="purchaseItemTemplate">
        <tr><td><select class="form-select purchase-product" required><option value="">Buscar/seleccionar producto</option>@foreach($products as $product)<option value="{{ $product->id }}" data-cost="{{ (float) $product->purchase_price }}" data-taxable="{{ $product->taxable ? '1' : '0' }}">{{ $product->nombre }} - {{ $product->barcode ?: $product->sku }}</option>@endforeach</select></td><td><input class="form-control purchase-quantity" type="number" min="1" value="1" required></td><td><input class="form-control purchase-cost" type="number" min="0" step="0.01" value="0" required></td><td class="text-end fw-bold purchase-line-total">C$ 0.00</td><td><button type="button" class="btn btn-outline-danger btn-sm purchase-remove">Quitar</button></td></tr>
    </template>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const items = document.getElementById('purchaseItems'), tpl = document.getElementById('purchaseItemTemplate'), taxRate = {{ $taxRate }};
            const money = v => `C$ ${Number(v || 0).toFixed(2)}`;
            const refreshNames = () => [...items.querySelectorAll('tr')].forEach((r,i)=>{r.querySelector('.purchase-product').name=`items[${i}][product_id]`;r.querySelector('.purchase-quantity').name=`items[${i}][quantity]`;r.querySelector('.purchase-cost').name=`items[${i}][unit_cost]`;});
            const recalc = () => {let sub=0,tax=0;items.querySelectorAll('tr').forEach(r=>{const opt=r.querySelector('.purchase-product').selectedOptions[0];const taxable=opt?.dataset.taxable==='1';const qty=Number(r.querySelector('.purchase-quantity').value||0);const cost=Number(r.querySelector('.purchase-cost').value||0);const line=qty*cost;const lineTax=taxable?line*taxRate:0;r.querySelector('.purchase-line-total').textContent=money(line+lineTax);sub+=line;tax+=lineTax;});document.getElementById('purchaseSubtotal').textContent=money(sub);document.getElementById('purchaseTax').textContent=money(tax);document.getElementById('purchaseTotal').textContent=money(sub+tax);};
            const addRow=()=>{items.appendChild(tpl.content.firstElementChild.cloneNode(true));refreshNames();recalc();};
            document.getElementById('addPurchaseItem').addEventListener('click',addRow);
            items.addEventListener('change',e=>{if(e.target.classList.contains('purchase-product')){e.target.closest('tr').querySelector('.purchase-cost').value=Number(e.target.selectedOptions[0]?.dataset.cost||0).toFixed(2);}recalc();});
            items.addEventListener('input',recalc);
            items.addEventListener('click',e=>{if(e.target.classList.contains('purchase-remove')){e.target.closest('tr').remove();refreshNames();recalc();}});
            addRow();
        });
    </script>
@endsection
