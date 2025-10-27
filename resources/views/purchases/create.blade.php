@extends('layout')
@section('content')
<h2>Nueva compra</h2>

<form method="POST" action="{{ route('purchases.store') }}" x-data="compra()">
  @csrf

<label>Forma de pago</label><br>
<select name="payment_term" x-model="term" required>
  <option value="CONTADO">CONTADO</option>
  <option value="CREDITO">CREDITO</option>
</select>
<div x-show="term==='CREDITO'" style="margin-top:6px;">
  <label>Vence</label>
  <input type="date" name="due_date" value="{{ date('Y-m-d') }}">
</div>


  <label>Proveedor</label><br>
  <select name="supplier_id" required>
    @foreach($suppliers as $s)
      <option value="{{ $s->id }}">{{ $s->name }}</option>
    @endforeach
  </select><br>

  <label>Bodega</label><br>
  <select name="warehouse_id" required>
    @foreach($warehouses as $w)
      <option value="{{ $w->id }}">{{ $w->name }}</option>
    @endforeach
  </select><br>

  <label>Fecha</label><br>
  <input type="date" name="date" value="{{ date('Y-m-d') }}" required><br><br>

  <h3>Items</h3>
  <table>
    <tr><th>Producto</th><th>Cant</th><th>Costo Unit</th><th>IVA %</th><th>Sub</th><th>Total</th><th></th></tr>
    <template x-for="(row,i) in items" :key="i">
      <tr>
        <td>
          <select :name="`items[${i}][product_id]`" x-model.number="row.product_id" required>
            @foreach($products as $p)
              <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->name }}</option>
            @endforeach
          </select>
        </td>
        <td><input type="number" step="0.001" :name="`items[${i}][qty]`" x-model.number="row.qty" @input="calc()" required></td>
        <td><input type="number" step="0.01"  :name="`items[${i}][unit_cost]`" x-model.number="row.unit_cost" @input="calc()" required></td>
        <td><input type="number" step="0.01"  :name="`items[${i}][tax_rate]`" x-model.number="row.tax_rate" @input="calc()"></td>
        <td x-text="fmt(row.qty*row.unit_cost)"></td>
        <td x-text="fmt((row.qty*row.unit_cost)*(1+row.tax_rate/100))"></td>
        <td><button type="button" @click="remove(i)">🗑</button></td>
      </tr>
    </template>
  </table>
  <button type="button" @click="add()">+ Agregar item</button>

  <h3>Totales</h3>
  <p>Subtotal: <b x-text="fmt(subtotal)"></b> | IVA: <b x-text="fmt(tax)"></b> | Total: <b x-text="fmt(total)"></b></p>

  <br><button type="submit">Guardar compra</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function compra(){
  return {
    term: 'CONTADO',
    
    items: [{ product_id: {{ $products->first()->id ?? 'null' }}, qty: 1, unit_cost: 0, tax_rate: 13 }],
    subtotal:0, tax:0, total:0,
    add(){ this.items.push({ product_id: {{ $products->first()->id ?? 'null' }}, qty:1, unit_cost:0, tax_rate:13 }); this.calc(); },
    remove(i){ this.items.splice(i,1); this.calc(); },
    calc(){
      let sub=0, t=0;
      this.items.forEach(r => { const s=r.qty*r.unit_cost; sub+=s; t += s*(r.tax_rate/100); });
      this.subtotal=sub; this.tax=t; this.total=sub+t;
    },
    fmt(n){ return (n||0).toFixed(2); }
  }
}
</script>
@endsection
