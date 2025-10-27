@extends('layout')
@section('content')
<h2>Nuevo pago a proveedor</h2>

<form method="POST" action="{{ route('payments.store') }}" x-data="pago()">
  @csrf

  <label>Proveedor</label><br>
  <select name="supplier_id" x-model.number="supplierId" @change="filtrar()" required>
    <option value="">-- Selecciona --</option>
    @foreach($suppliers as $s)
      <option value="{{ $s->id }}">{{ $s->name }}</option>
    @endforeach
  </select><br><br>

  <label>Compra (opcional, crédito)</label><br>
  <select name="purchase_id" x-model.number="purchaseId">
    <option value="">-- Sin ligar a compra --</option>
    @foreach($creditPurchases as $p)
      <option value="{{ $p->id }}" :data-supplier="{{ $p->supplier_id }}">
        #{{ $p->id }} | {{ $p->supplier_name }} | {{ $p->date }} | Pendiente: ${{ number_format($p->outstanding,2) }}
      </option>
    @endforeach
  </select>
  <small>Si eliges una compra a crédito, el pago se limita al saldo pendiente.</small>
  <br><br>

  <label>Cuenta de pago (Caja/Bancos)</label><br>
  <select name="account_id_cash_or_bank" required>
    @foreach($cashAccounts as $a)
      <option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>
    @endforeach
  </select><br><br>

  <label>Fecha</label><br>
  <input type="date" name="date" value="{{ date('Y-m-d') }}" required><br><br>

  <label>Monto</label><br>
  <input type="number" step="0.01" name="amount" placeholder="0.00" required><br><br>

  <button type="submit">Guardar pago</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function pago(){
  return {
    supplierId:'', purchaseId:'',
    filtrar(){
      const sel = document.querySelector('select[name="purchase_id"]');
      const opts = sel.querySelectorAll('option[value]');
      opts.forEach(o=>{
        if(!o.value) return;
        const sup = parseInt(o.getAttribute('data-supplier'));
        o.hidden = this.supplierId && (sup !== parseInt(this.supplierId));
        if(o.hidden && sel.value === o.value){ sel.value=''; this.purchaseId=''; }
      });
    }
  }
}
</script>
@endsection
