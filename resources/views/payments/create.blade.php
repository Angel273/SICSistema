@extends('layout')

@section('content')
<h1 style="margin-bottom:12px;">Registrar pago a proveedor</h1>

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

<form method="POST" action="{{ route('payments.store') }}" id="paymentForm">
  @csrf

  <div class="grid" style="display:grid; gap:16px; grid-template-columns: 2fr 1fr;">

    <!-- Columna izquierda -->
    <div style="display:grid; gap:16px;">

      <div class="card" style="padding:16px;">
        <h3>Datos del pago</h3>
        <div style="display:grid; gap:12px; grid-template-columns: repeat(2, 1fr);">
          <label style="grid-column: span 2;">
            <span>Proveedor</span>
            <select name="supplier_id" id="supplier_id" required>
              <option value="">— Seleccione —</option>
              @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id')==$s->id)>{{ $s->name }}</option>
              @endforeach
            </select>
          </label>

          <label>
            <span>Fecha</span>
            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
          </label>

          <label>
            <span>Caja / Banco (sale dinero)</span>
            <select name="account_id_cash_or_bank" id="account_id_cash_or_bank" required>
              <option value="">— Seleccione —</option>
              @foreach($cashAccounts as $acc)
                <option value="{{ $acc->id }}" @selected(old('account_id_cash_or_bank')==$acc->id)>{{ $acc->code }} — {{ $acc->name }}</option>
              @endforeach
            </select>
          </label>

          <label>
            <span>Monto</span>
            <input type="number" name="amount" id="amount" step="0.01" min="0.01" value="{{ old('amount','0.00') }}" required>
          </label>

          <label>
            <span>Vincular a compra (opcional)</span>
            <select name="purchase_id" id="purchase_id">
              <option value="">— Sin vincular —</option>
              <!-- se llena por JS según proveedor -->
            </select>
          </label>

          <label>
            <span>Notas (opcional)</span>
            <input type="text" name="notes" maxlength="255" value="{{ old('notes') }}" placeholder="N° cheque, ref. bancaria, etc.">
          </label>
        </div>
      </div>

      <div class="card" style="padding:16px;">
        <h3>Compras a crédito pendientes del proveedor</h3>
        <div style="overflow:auto; margin-top:12px;">
          <table class="table" id="pendingTable">
            <thead>
              <tr>
                <th>Compra</th>
                <th>Fecha</th>
                <th style="text-align:right;">Total</th>
                <th style="text-align:right;">Pendiente</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="5" style="opacity:.7;">Seleccione un proveedor para ver sus compras pendientes…</td></tr>
            </tbody>
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
            <span>Proveedor:</span><strong id="sum_supplier">—</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Compra vinculada:</span><strong id="sum_purchase">—</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Pendiente de la compra:</span><strong id="sum_outstanding">0.00</strong>
          </div>
          <hr>
          <div style="display:flex; justify-content:space-between; font-size:1.1em;">
            <span>Monto a pagar:</span><strong id="sum_amount">0.00</strong>
          </div>
        </div>
      </div>

      <div class="card" style="padding:16px;">
        <button type="submit" class="btn" style="width:100%; padding:12px; font-weight:700;">Guardar pago</button>
      </div>
    </div>

  </div>

  <!-- JSON embebido de compras a crédito -->
  <script type="application/json" id="creditPurchasesJSON">
    {!! json_encode($creditPurchases, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
  </script>
</form>

<script>
(function(){
  const $  = (q,ctx=document)=>ctx.querySelector(q);
  const $$ = (q,ctx=document)=>Array.from(ctx.querySelectorAll(q));
  const money = n => (Math.round((+n + Number.EPSILON)*100)/100).toFixed(2);

  const rowsRaw = JSON.parse(document.getElementById('creditPurchasesJSON').textContent || '[]');

  const supplierSel = $('#supplier_id');
  const purchaseSel = $('#purchase_id');
  const amountInp   = $('#amount');

  const sumSupplier = $('#sum_supplier');
  const sumPurchase = $('#sum_purchase');
  const sumOut      = $('#sum_outstanding');
  const sumAmount   = $('#sum_amount');

  function syncSummary(){
    sumSupplier.textContent = supplierSel.selectedOptions[0]?.text || '—';
    const opt = purchaseSel.selectedOptions[0];
    sumPurchase.textContent = opt && opt.value ? opt.text : '—';
    sumAmount.textContent = money(amountInp.value || 0);
  }

  function fillPurchasesForSupplier(){
    const sid = parseInt(supplierSel.value || 0);
    purchaseSel.innerHTML = '<option value="">— Sin vincular —</option>';
    const tbody = $('#pendingTable tbody');
    tbody.innerHTML = '';

    if(!sid){
      tbody.innerHTML = '<tr><td colspan="5" style="opacity:.7;">Seleccione un proveedor para ver sus compras pendientes…</td></tr>';
      setOutstanding(null);
      return;
    }

    const rows = rowsRaw.filter(p => +p.supplier_id === sid);

    if(rows.length === 0){
      tbody.innerHTML = '<tr><td colspan="5" style="opacity:.7;">Este proveedor no tiene compras a crédito pendientes.</td></tr>';
    } else {
      rows.forEach(p => {
        const label = `#${p.id} — ${p.date} — Pendiente: $${money(p.outstanding)}`;
        const op = document.createElement('option');
        op.value = p.id; op.textContent = label; op.dataset.outstanding = p.outstanding;
        purchaseSel.appendChild(op);

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>#${p.id}</td>
          <td>${p.date}</td>
          <td style="text-align:right;">$${money(p.total)}</td>
          <td style="text-align:right;">$${money(p.outstanding)}</td>
          <td style="text-align:center;">
            <button type="button" class="btn btnMini" data-pick="${p.id}">Aplicar</button>
          </td>
        `;
        tbody.appendChild(tr);
      });

      $$('#pendingTable [data-pick]').forEach(btn=>{
        btn.addEventListener('click', ()=>{
          const id = btn.getAttribute('data-pick');
          purchaseSel.value = id;
          purchaseSel.dispatchEvent(new Event('change'));
        });
      });
    }

    purchaseSel.value = '';
    setOutstanding(null);
  }

  function setOutstanding(amt){
    const out = amt ? parseFloat(amt) : 0;
    sumOut.textContent = money(out);
    if(out > 0){
      amountInp.max = out;
      if(parseFloat(amountInp.value||0) > out){ amountInp.value = out; }
    } else {
      amountInp.removeAttribute('max');
    }
    syncSummary();
  }

  // Eventos
  supplierSel.addEventListener('change', ()=>{ fillPurchasesForSupplier(); syncSummary(); });
  purchaseSel.addEventListener('change', ()=>{
    const opt = purchaseSel.selectedOptions[0];
    const out = opt && opt.dataset.outstanding ? parseFloat(opt.dataset.outstanding) : 0;
    setOutstanding(out);
  });
  amountInp.addEventListener('input', ()=>{
    const max = parseFloat(amountInp.max || 0);
    let val = parseFloat(amountInp.value || 0);
    if(max && val > max){ val = max; amountInp.value = val; }
    syncSummary();
  });

  // Init
  fillPurchasesForSupplier();
  syncSummary();

  // Mini estilos
  const style = document.createElement('style');
  style.textContent = `
    .btnMini { padding: 6px 10px; border-radius: 8px; background:#0ea5e9; color:#fff; border:1px solid transparent; }
    .btnMini:hover { filter: brightness(.95); }
  `;
  document.head.appendChild(style);

  // Validación simple antes de enviar
  $('#paymentForm').addEventListener('submit', (e)=>{
    const amount = parseFloat(amountInp.value || 0);
    if(amount < 0.01){
      e.preventDefault();
      alert('El monto debe ser >= 0.01');
    }
  });
})();
</script>
@endsection

