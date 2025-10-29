@extends('layout')

@section('content')
<h1 style="margin-bottom:12px;">Registrar cobro</h1>

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

<form method="POST" action="{{ route('receipts.store') }}" id="receiptForm">
  @csrf

  <div class="grid" style="display:grid; gap:16px; grid-template-columns: 2fr 1fr;">

    <!-- Columna izquierda -->
    <div style="display:grid; gap:16px;">

      <div class="card" style="padding:16px;">
        <h3>Datos del cobro</h3>
        <div style="display:grid; gap:12px; grid-template-columns: repeat(2, 1fr);">
          <label style="grid-column: span 2;">
            <span>Cliente</span>
            <select name="customer_id" id="customer_id" required>
              <option value="">— Seleccione —</option>
              @foreach($customers as $c)
                <option value="{{ $c->id }}" @selected(old('customer_id')==$c->id)>{{ $c->name }}</option>
              @endforeach
            </select>
          </label>

          <label>
            <span>Fecha</span>
            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
          </label>

          <label>
            <span>Caja / Banco</span>
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
            <span>Vincular a venta (opcional)</span>
            <select name="sale_id" id="sale_id">
              <option value="">— Sin vincular —</option>
              <!-- se llenará según cliente -->
            </select>
          </label>

          <label>
            <span>Notas (opcional)</span>
            <input type="text" name="notes" maxlength="255" value="{{ old('notes') }}" placeholder="Referencia, número de recibo físico, etc.">
          </label>
        </div>
      </div>

      <div class="card" style="padding:16px;">
        <h3>Ventas a crédito pendientes del cliente</h3>
        <div style="overflow:auto; margin-top:12px;">
          <table class="table" id="pendingTable">
            <thead>
              <tr>
                <th>Venta</th>
                <th>Fecha</th>
                <th style="text-align:right;">Total</th>
                <th style="text-align:right;">Pendiente</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="5" style="opacity:.7;">Seleccione un cliente para ver sus ventas pendientes…</td></tr>
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
            <span>Cliente:</span><strong id="sum_customer">—</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Venta vinculada:</span><strong id="sum_sale">—</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Pendiente de la venta:</span><strong id="sum_outstanding">0.00</strong>
          </div>
          <hr>
          <div style="display:flex; justify-content:space-between; font-size:1.1em;">
            <span>Monto a registrar:</span><strong id="sum_amount">0.00</strong>
          </div>
        </div>
      </div>

      <div class="card" style="padding:16px;">
        <button type="submit" class="btn" style="width:100%; padding:12px; font-weight:700;">Guardar cobro</button>
      </div>
    </div>

  </div>

  <!-- JSON embebido de ventas a crédito -->
  <script type="application/json" id="creditSalesJSON">
    {!! json_encode($creditSales, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
  </script>
</form>

<script>
(function(){
  const $  = (q,ctx=document)=>ctx.querySelector(q);
  const $$ = (q,ctx=document)=>Array.from(ctx.querySelectorAll(q));
  const money = n => (Math.round((+n + Number.EPSILON)*100)/100).toFixed(2);

  const salesRaw = JSON.parse(document.getElementById('creditSalesJSON').textContent || '[]');

  const customerSel = $('#customer_id');
  const saleSel     = $('#sale_id');
  const amountInp   = $('#amount');

  const sumCustomer = $('#sum_customer');
  const sumSale     = $('#sum_sale');
  const sumOut      = $('#sum_outstanding');
  const sumAmount   = $('#sum_amount');

  function syncSummary(){
    sumCustomer.textContent = customerSel.selectedOptions[0]?.text || '—';
    const opt = saleSel.selectedOptions[0];
    sumSale.textContent = opt && opt.value ? opt.text : '—';
    sumAmount.textContent = money(amountInp.value || 0);
  }

  function fillSalesForCustomer(){
    const cid = parseInt(customerSel.value || 0);
    saleSel.innerHTML = '<option value="">— Sin vincular —</option>';

    const tbody = $('#pendingTable tbody');
    tbody.innerHTML = '';

    if(!cid){
      tbody.innerHTML = '<tr><td colspan="5" style="opacity:.7;">Seleccione un cliente para ver sus ventas pendientes…</td></tr>';
      setOutstanding(null);
      return;
    }

    const rows = salesRaw.filter(s => +s.customer_id === cid);

    if(rows.length === 0){
      tbody.innerHTML = '<tr><td colspan="5" style="opacity:.7;">Este cliente no tiene ventas a crédito pendientes.</td></tr>';
    } else {
      rows.forEach(s => {
        // Option al selector
        const label = `#${s.id} — ${s.date} — Pendiente: $${money(s.outstanding)}`;
        const op = document.createElement('option');
        op.value = s.id; op.textContent = label; op.dataset.outstanding = s.outstanding;
        saleSel.appendChild(op);

        // Fila en tabla
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>#${s.id}</td>
          <td>${s.date}</td>
          <td style="text-align:right;">$${money(s.total)}</td>
          <td style="text-align:right;">$${money(s.outstanding)}</td>
          <td style="text-align:center;">
            <button type="button" class="btn btnMini" data-pick="${s.id}">Aplicar</button>
          </td>
        `;
        tbody.appendChild(tr);
      });

      // Hook a los botones "Aplicar"
      $$('#pendingTable [data-pick]').forEach(btn=>{
        btn.addEventListener('click', ()=>{
          const id = btn.getAttribute('data-pick');
          saleSel.value = id;
          saleSel.dispatchEvent(new Event('change'));
        });
      });
    }

    // Default: sin selección
    saleSel.value = '';
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
  customerSel.addEventListener('change', ()=>{ fillSalesForCustomer(); syncSummary(); });
  saleSel.addEventListener('change', ()=>{
    const opt = saleSel.selectedOptions[0];
    const out = opt && opt.dataset.outstanding ? parseFloat(opt.dataset.outstanding) : 0;
    setOutstanding(out);
  });
  amountInp.addEventListener('input', ()=>{
    // respetar max si existe
    const max = parseFloat(amountInp.max || 0);
    let val = parseFloat(amountInp.value || 0);
    if(max && val > max){ val = max; amountInp.value = val; }
    if(val < 0.01){ /* no forzamos aún, solo resumen */ }
    syncSummary();
  });

  // Init
  fillSalesForCustomer();
  syncSummary();

  // Mini estilos
  const style = document.createElement('style');
  style.textContent = `
    .btnMini { padding: 6px 10px; border-radius: 8px; background:#0ea5e9; color:#fff; border:1px solid transparent; }
    .btnMini:hover { filter: brightness(.95); }
  `;
  document.head.appendChild(style);

  // Validación simple antes de enviar
  $('#receiptForm').addEventListener('submit', (e)=>{
    const amount = parseFloat(amountInp.value || 0);
    if(amount < 0.01){
      e.preventDefault();
      alert('El monto debe ser >= 0.01');
    }
  });
})();
</script>
@endsection
