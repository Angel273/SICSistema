@extends('layout')

@section('content')
<h1 style="margin-bottom:12px;">Registrar venta</h1>

@if ($errors->any())
  <div class="card" style="border-left:4px solid #e11d48; padding:12px; background:#fff5f5;">
    <strong>Hay errores en el formulario:</strong>
    <ul style="margin:6px 0 0 18px;">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('sales.store') }}" id="saleForm">
  @csrf

  <div class="grid" style="display:grid; gap:16px; grid-template-columns: 2fr 1fr;">

    <!-- Columna izquierda -->
    <div style="display:grid; gap:16px;">

      <div class="card" style="padding:16px;">
        <h3>Encabezado</h3>
        <div class="form-grid" style="display:grid; gap:12px; grid-template-columns: repeat(2, 1fr);">
          <label>
            <span>Cliente</span>
            <select name="customer_id" required>
              <option value="">— Seleccione —</option>
              @foreach($customers as $c)
                <option value="{{ $c->id }}" @selected(old('customer_id')==$c->id)>{{ $c->name }}</option>
              @endforeach
            </select>
          </label>

          <label>
            <span>Tienda</span>
            <select name="store_id" required>
              <option value="">— Seleccione —</option>
              @foreach($stores as $s)
                <option value="{{ $s->id }}" @selected(old('store_id')==$s->id)>{{ $s->name }}</option>
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
              <option value="CREDITO"  @selected(old('payment_term')==='CREDITO')>CRÉDITO</option>
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
          <table class="table" id="itemsTable">
            <thead>
              <tr>
                <th>Producto</th>
                <th style="text-align:right;">Cantidad</th>
                <th style="text-align:right;">Precio unit.</th>
                <th style="text-align:right;">Desc. unit.</th>
                <th style="text-align:right;">IVA (%)</th>
                <th style="text-align:right;">Subtotal</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <!-- filas por JS -->
            </tbody>
            <tfoot>
              <tr>
                <td colspan="5" style="text-align:right; font-weight:600;">Subtotal</td>
                <td style="text-align:right;"><span id="t_subtotal">0.00</span></td>
                <td></td>
              </tr>
              <tr>
                <td colspan="5" style="text-align:right; font-weight:600;">IVA</td>
                <td style="text-align:right;"><span id="t_tax">0.00</span></td>
                <td></td>
              </tr>
              <tr>
                <td colspan="5" style="text-align:right; font-weight:700; font-size:1.1em;">Total</td>
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
            <span>Cliente:</span><strong id="sum_customer">—</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Tienda:</span><strong id="sum_store">—</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Pago:</span><strong id="sum_term">CONTADO</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Vence:</span><strong id="sum_due">—</strong>
          </div>
          <hr>
          <div style="display:flex; justify-content:space-between;">
            <span>Subtotal:</span><strong id="sum_subtotal">0.00</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>IVA:</span><strong id="sum_tax">0.00</strong>
          </div>
          <div style="display:flex; justify-content:space-between; font-size:1.2em;">
            <span>Total:</span><strong id="sum_total">0.00</strong>
          </div>
        </div>
      </div>

      <div class="card" style="padding:16px;">
        <button type="submit" class="btn" style="width:100%; padding:12px; font-weight:700;">Guardar venta</button>
      </div>
    </div>

  </div>

  <!-- Template de fila -->
  <template id="rowTpl">
    <tr>
      <td>
        <select name="items[IDX][product_id]" required>
          <option value="">— Seleccione —</option>
          @foreach($products as $p)
            <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option>
          @endforeach
        </select>
      </td>
      <td style="text-align:right;">
        <input type="number" name="items[IDX][qty]" step="0.001" min="0.001" value="1" class="qty" style="text-align:right;" required>
      </td>
      <td style="text-align:right;">
        <input type="number" name="items[IDX][unit_price]" step="0.01" min="0" value="0.00" class="uprice" style="text-align:right;" required>
      </td>
      <td style="text-align:right;">
        <input type="number" name="items[IDX][discount]" step="0.01" min="0" value="0.00" class="udiscount" style="text-align:right;">
      </td>
      <td style="text-align:right;">
        <input type="number" name="items[IDX][tax_rate]" step="0.01" min="0" value="13" class="taxrate" style="text-align:right;">
      </td>
      <td style="text-align:right;">
        <span class="lineSub">0.00</span>
      </td>
      <td style="text-align:center;">
        <button type="button" class="btn btnDel" title="Eliminar">🗑️</button>
      </td>
    </tr>
  </template>

</form>

<script>
(function(){
  const $  = (q,ctx=document)=>ctx.querySelector(q);
  const $$ = (q,ctx=document)=>Array.from(ctx.querySelectorAll(q));

  const itemsTbody = $('#itemsTable tbody');
  const btnAdd = $('#btnAdd');
  let idx = 0;

  function money(n){ return (Math.round((+n + Number.EPSILON)*100)/100).toFixed(2); }

  function addRow() {
    // Inserta la plantilla reemplazando IDX por un índice único
    const html = document.querySelector('#rowTpl').content.firstElementChild
      .outerHTML.replaceAll('IDX', idx++);
    itemsTbody.insertAdjacentHTML('beforeend', html);

    const tr = itemsTbody.lastElementChild;
    bindRow(tr);
    recalc();
  }

  function bindRow(tr){
    const qty = $('.qty', tr);
    const uprice = $('.uprice', tr);
    const udiscount = $('.udiscount', tr);
    const taxrate = $('.taxrate', tr);
    const del = $('.btnDel', tr);

    [qty, uprice, udiscount, taxrate].forEach(inp => inp.addEventListener('input', recalc));
    del.addEventListener('click', ()=>{ tr.remove(); recalc(); });
  }

  function recalc(){
    let sub=0, iva=0;

    $$('#itemsTable tbody tr').forEach(tr=>{
      const qty = parseFloat($('.qty', tr).value||0);
      const price = parseFloat($('.uprice', tr).value||0);
      const disc  = parseFloat($('.udiscount', tr).value||0);
      const rate  = parseFloat($('.taxrate', tr).value||0);

      const netUnit = Math.max(0, price - disc);
      const lineSub = qty*netUnit;
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
  const customerSel = document.querySelector('select[name="customer_id"]');
  const storeSel    = document.querySelector('select[name="store_id"]');
  const termSel     = $('#payment_term');
  const dueInput    = $('#due_date');
  const dueWrap     = $('#dueWrapper');

  function syncSummary(){
    $('#sum_customer').textContent = customerSel.selectedOptions[0]?.text || '—';
    $('#sum_store').textContent    = storeSel.selectedOptions[0]?.text || '—';
    $('#sum_term').textContent     = termSel.value;
    $('#sum_due').textContent      = dueInput.value || '—';
  }

  customerSel.addEventListener('change', syncSummary);
  storeSel.addEventListener('change', syncSummary);
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
  $('#saleForm').addEventListener('submit', (e)=>{
    if ($$('#itemsTable tbody tr').length === 0){
      e.preventDefault();
      alert('Agregá al menos un ítem.');
    }
  });

})();
</script>
@endsection

