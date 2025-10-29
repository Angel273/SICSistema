@extends('layout')

@section('content')
<h1 style="margin-bottom:12px;">Registrar compra</h1>

@if ($errors->any())
  <div class="card" style="border-left:4px solid #e63946; padding:12px; background:#fee;">
    <strong>Hay errores en el formulario:</strong>
    <ul style="margin:6px 0 0 18px;">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('purchases.store') }}" id="purchaseForm">
  @csrf

  <div class="grid" style="display:grid; gap:16px; grid-template-columns: 2fr 1fr;">
    <!-- Columna izquierda -->
    <div style="display:grid; gap:16px;">
      <div class="card" style="padding:16px;">
        <h3>Encabezado</h3>
        <div class="form-grid" style="display:grid; gap:12px; grid-template-columns: repeat(2, 1fr);">
          <label>
            <span>Proveedor</span>
            <select name="supplier_id" required>
              <option value="">— Seleccione —</option>
              @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id')==$s->id)>{{ $s->name }}</option>
              @endforeach
            </select>
          </label>

          <label>
            <span>Bodega</span>
            <select name="warehouse_id" required>
              <option value="">— Seleccione —</option>
              @foreach($warehouses as $w)
                <option value="{{ $w->id }}" @selected(old('warehouse_id')==$w->id)>{{ $w->name }}</option>
              @endforeach
            </select>
          </label>

          <label>
            <span>Fecha</span>
            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
          </label>

          <label>
            <span>Condición de pago</span>
            <select name="payment_term" id="payment_term" required>
              <option value="CONTADO" @selected(old('payment_term')==='CONTADO')>CONTADO</option>
              <option value="CREDITO" @selected(old('payment_term')==='CREDITO')>CRÉDITO</option>
            </select>
          </label>

          <label id="dueWrapper" style="grid-column: span 2;">
            <span>Fecha de vencimiento (solo crédito)</span>
            <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}">
          </label>
        </div>
      </div>

      <div class="card" style="padding:16px;">
        <div style="display:flex; align-items:center; justify-content:space-between;">
          <h3 style="margin:0;">Ítems</h3>
          <button type="button" class="btn" id="btnAdd" style="padding:8px 12px;">+ Agregar</button>
        </div>

        <div style="overflow:auto; margin-top:12px;">
          <table class="table" id="itemsTable" style="width:100%; border-collapse:collapse;">
            <thead>
              <tr>
                <th style="text-align:left;">Producto</th>
                <th style="text-align:right;">Cantidad</th>
                <th style="text-align:right;">Costo unit.</th>
                <th style="text-align:right;">IVA (%)</th>
                <th style="text-align:right;">Subtotal</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <!-- filas se generan por JS -->
            </tbody>
            <tfoot>
              <tr>
                <td colspan="4" style="text-align:right; font-weight:600;">Subtotal</td>
                <td style="text-align:right;"><span id="t_subtotal">0.00</span></td>
                <td></td>
              </tr>
              <tr>
                <td colspan="4" style="text-align:right; font-weight:600;">IVA</td>
                <td style="text-align:right;"><span id="t_tax">0.00</span></td>
                <td></td>
              </tr>
              <tr>
                <td colspan="4" style="text-align:right; font-weight:700; font-size:1.1em;">Total</td>
                <td style="text-align:right; font-weight:700; font-size:1.1em;"><span id="t_total">0.00</span></td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- Columna derecha -->
    <div style="display:grid; gap:16px;">
      <div class="card" style="padding:16px;">
        <h3>Resumen</h3>
        <div style="display:grid; gap:8px;">
          <div style="display:flex; justify-content:space-between;">
            <span>Proveedor:</span>
            <strong id="sum_supplier">—</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Bodega:</span>
            <strong id="sum_warehouse">—</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Pago:</span>
            <strong id="sum_term">CONTADO</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Vence:</span>
            <strong id="sum_due">—</strong>
          </div>
          <hr>
          <div style="display:flex; justify-content:space-between;">
            <span>Subtotal:</span>
            <strong id="sum_subtotal">0.00</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>IVA:</span>
            <strong id="sum_tax">0.00</strong>
          </div>
          <div style="display:flex; justify-content:space-between; font-size:1.2em;">
            <span>Total:</span>
            <strong id="sum_total">0.00</strong>
          </div>
        </div>
      </div>

      <div class="card" style="padding:16px;">
        <button type="submit" class="btn" style="width:100%; padding:12px; font-weight:700;">Guardar compra</button>
      </div>
    </div>
  </div>

  <!-- plantilla oculta para filas -->
  <template id="rowTpl">
    <tr>
      <td>
        <select name="items[IDX][product_id]" required class="prodSel">
          <option value="">— Seleccione —</option>
          @foreach($products as $p)
            <option value="{{ $p->id }}" data-sku="{{ $p->sku }}">{{ $p->sku }} — {{ $p->name }}</option>
          @endforeach
        </select>
      </td>
      <td style="text-align:right;">
        <input type="number" name="items[IDX][qty]" step="0.001" min="0.001" value="1" class="qty" style="text-align:right;" required>
      </td>
      <td style="text-align:right;">
        <input type="number" name="items[IDX][unit_cost]" step="0.01" min="0" value="0.00" class="ucost" style="text-align:right;" required>
      </td>
      <td style="text-align:right;">
        <input type="number" name="items[IDX][tax_rate]" step="0.01" min="0" value="13" class="taxrate" style="text-align:right;">
      </td>
      <td style="text-align:right;">
        <span class="lineSub">0.00</span>
      </td>
      <td style="text-align:center;">
        <button type="button" class="btn btnDel" title="Eliminar" style="padding:4px 8px;">🗑️</button>
      </td>
    </tr>
  </template>

</form>

<script>
(function(){
  const $ = (q,ctx=document)=>ctx.querySelector(q);
  const $$ = (q,ctx=document)=>Array.from(ctx.querySelectorAll(q));

  const itemsTbody = $('#itemsTable tbody');
  const btnAdd = $('#btnAdd');
  let idx = 0;

  function money(n){ return (Math.round((+n + Number.EPSILON)*100)/100).toFixed(2); }

  function addRow() {
    // Toma el HTML del template y reemplaza IDX por el índice
    const html = document.querySelector('#rowTpl').content.firstElementChild
      .outerHTML.replaceAll('IDX', idx++);

    // Inserta la fila directamente en el <tbody> (sin wrappers)
    const itemsTbody = document.querySelector('#itemsTable tbody');
    itemsTbody.insertAdjacentHTML('beforeend', html);

    // Bindea eventos a la última fila insertada
    const tr = itemsTbody.lastElementChild;
    bindRow(tr);
    recalc();
  }

  function bindRow(tr){
    const qty = $('.qty', tr);
    const ucost = $('.ucost', tr);
    const taxrate = $('.taxrate', tr);
    const del = $('.btnDel', tr);
    [qty, ucost, taxrate].forEach(inp => inp.addEventListener('input', recalc));
    del.addEventListener('click', ()=>{ tr.remove(); recalc(); });
  }

  function recalc(){
    let sub=0, iva=0;

    $$('#itemsTable tbody tr').forEach(tr=>{
      const qty = parseFloat($('.qty', tr).value||0);
      const ucost = parseFloat($('.ucost', tr).value||0);
      const rate = parseFloat($('.taxrate', tr).value||0);
      const lineSub = qty*ucost;
      const lineTax = lineSub*(rate/100);
      sub += lineSub;
      iva += lineTax;
      $('.lineSub', tr).textContent = money(lineSub);
    });

    const total = sub + iva;

    // Totales pie
    $('#t_subtotal').textContent = money(sub);
    $('#t_tax').textContent      = money(iva);
    $('#t_total').textContent    = money(total);

    // Resumen
    $('#sum_subtotal').textContent = money(sub);
    $('#sum_tax').textContent      = money(iva);
    $('#sum_total').textContent    = money(total);
  }

  // Encabezado -> Resumen
  const supplierSel = document.querySelector('select[name="supplier_id"]');
  const warehouseSel = document.querySelector('select[name="warehouse_id"]');
  const termSel = $('#payment_term');
  const dueInput = $('#due_date');
  const dueWrap = $('#dueWrapper');

  function syncSummary(){
    $('#sum_supplier').textContent = supplierSel.selectedOptions[0]?.text || '—';
    $('#sum_warehouse').textContent = warehouseSel.selectedOptions[0]?.text || '—';
    $('#sum_term').textContent = termSel.value;
    $('#sum_due').textContent = dueInput.value || '—';
  }

  supplierSel.addEventListener('change', syncSummary);
  warehouseSel.addEventListener('change', syncSummary);
  termSel.addEventListener('change', ()=>{
    const isCred = termSel.value === 'CREDITO';
    dueInput.disabled = !isCred;
    dueWrap.style.opacity = isCred ? '1' : '0.5';
    if(!isCred){ dueInput.value=''; }
    syncSummary();
  });
  dueInput.addEventListener('change', syncSummary);

  // Init
  termSel.dispatchEvent(new Event('change'));
  addRow();

  // Botón agregar
  btnAdd.addEventListener('click', addRow);

  // Validación mínima antes de enviar
  $('#purchaseForm').addEventListener('submit', (e)=>{
    if ($$('#itemsTable tbody tr').length === 0){
      e.preventDefault();
      alert('Agregá al menos un ítem.');
    }
  });

})();
</script>
@endsection
