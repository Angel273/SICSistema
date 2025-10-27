@extends('layout')
@section('content')
<h2>Nuevo cobro</h2>

<form method="POST" action="{{ route('receipts.store') }}" x-data="recibo()">
  @csrf

  <label>Cliente</label><br>
  <select name="customer_id" x-model.number="customerId" @change="filtrar()" required>
    <option value="">-- Selecciona --</option>
    @foreach($customers as $c)
      <option value="{{ $c->id }}">{{ $c->name }}</option>
    @endforeach
  </select><br><br>

  <label>Venta (opcional, crédito)</label><br>
  <select name="sale_id" x-model.number="saleId">
    <option value="">-- Sin ligar a venta --</option>
    @foreach($creditSales as $s)
      <option value="{{ $s->id }}" :data-customer="{{ $s->customer_id }}">
        #{{ $s->id }} | {{ $s->customer_name }} | {{ $s->date }} | Pendiente: ${{ number_format($s->outstanding,2) }}
      </option>
    @endforeach
  </select>
  <small>Si eliges una venta, el sistema limitará el cobro al saldo pendiente.</small>
  <br><br>

  <label>Cuenta de ingreso (Caja/Bancos)</label><br>
  <select name="account_id_cash_or_bank" required>
    @foreach($cashAccounts as $a)
      <option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>
    @endforeach
  </select><br><br>

  <label>Fecha</label><br>
  <input type="date" name="date" value="{{ date('Y-m-d') }}" required><br><br>

  <label>Monto</label><br>
  <input type="number" step="0.01" name="amount" placeholder="0.00" required><br><br>

  <label>Notas</label><br>
  <input type="text" name="notes" maxlength="255" placeholder="Opcional"><br><br>

  <button type="submit">Guardar cobro</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function recibo(){
  return {
    customerId: '', saleId: '',
    filtrar(){
      // Filtra la lista de ventas por cliente en el front (rápido)
      const sel = document.querySelector('select[name="sale_id"]');
      const opts = sel.querySelectorAll('option[value]');
      opts.forEach(o=>{
        if(!o.value) return;
        const cust = parseInt(o.getAttribute('data-customer'));
        o.hidden = this.customerId && (cust !== parseInt(this.customerId));
        if(o.hidden && sel.value === o.value){ sel.value=''; this.saleId=''; }
      });
    }
  }
}
</script>
@endsection
