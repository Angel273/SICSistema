@extends('layout')
@section('content')
<h2>Nueva venta</h2>

<form method="POST" action="{{ route('sales.store') }}" x-data="venta()">
  @csrf

  <label>Cliente</label><br>
  <select name="customer_id" required>
    @foreach($customers as $c)
      <option value="{{ $c->id }}">{{ $c->name }}</option>
    @endforeach
  </select><br><br>

  <label>Tienda</label><br>
  <select name="store_id" required>
    @foreach($stores as $s)
      <option value="{{ $s->id }}">{{ $s->name }}</option>
    @endforeach
  </select><br><br>

  <label>Fecha</label><br>
  <input type="date" name="date" value="{{ date('Y-m-d') }}" required><br><br>

  <label>Forma de pago</label><br>
  <select name="payment_term" x-model="term" @change="toggleDue()" required>
    <option value="CONTADO">CONTADO</option>
    <option value="CREDITO">CREDITO</option>
  </select>
  <div x-show="term==='CREDITO'" style="margin-top:6px;">
    <label>Vence</label>
    <input type="date" name="due_date" value="{{ date('Y-m-d') }}">
  </div>
  <br>

  <h3>Items</h3>
  <table>
    <tr><th>Producto</th><th>Cant</th><th>Precio</th><th>Desc</th><th>IVA %</th><th>Sub</th><th>Total</th><th></th></tr>
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
        <td><input type="number" step="0.01"  :name="`items[${i}][unit_price]`" x-model.number="row.unit_price" @input="calc()" required></td>
        <td><input type="number" step="0.01"  :name="`items[${i}][discount]`" x-model.number="row.discount" @input="calc()"></td>
        <td><input type="number" step="0.01"  :name="`items[${i}][tax_rate]`" x-model.number="row.tax_rate" @input="calc()"></td>
        <td x-text="fmt(row.qty * Math.max(0,row.unit_price-row.discount))"></td>
        <td x-text="fmt((row.qty * Math.max(0,row.unit_price-row.discount))*(1+row.tax_rate/100))"></td>
        <td><button type="button" @click="remove(i)">🗑</button></td>
      </tr>
    </template>
  </table>
  <button type="button" @click="add()">+ Agregar item</button>

  <h3>Totales</h3>
  <p>Subtotal: <b x-text="fmt(subtotal)"></b> | IVA: <b x-text="fmt(tax)"></b> | Total: <b x-text="fmt(total)"></b></p>

  <br><button type="submit">Guardar venta</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function venta(){
  return {
    term:'CONTADO',
    items:[{ product_id: {{ $products->first()->id ?? 'null' }}, qty:1, unit_price:0, discount:0, tax_rate:13 }],
    subtotal:0, tax:0, total:0,
    add(){ this.items.push({ product_id: {{ $products->first()->id ?? 'null' }}, qty:1, unit_price:0, discount:0, tax_rate:13 }); this.calc(); },
    remove(i){ this.items.splice(i,1); this.calc(); },
    calc(){
      let sub=0, t=0;
      this.items.forEach(r=>{
        const net = Math.max(0, r.unit_price - r.discount);
        const s = r.qty * net; sub += s; t += s*(r.tax_rate/100);
      });
      this.subtotal=sub; this.tax=t; this.total=sub+t;
    },
    fmt(n){ return (n||0).toFixed(2); },
    toggleDue(){} // placeholder
  }
}
</script>
@endsection
