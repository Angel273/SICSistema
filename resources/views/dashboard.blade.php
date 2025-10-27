@extends('layout')
@section('content')

<h1>Dashboard</h1>

<form method="GET" action="{{ route('dashboard') }}" style="margin:12px 0;">
  <label>Desde</label>
  <input type="date" name="desde" value="{{ $desde }}">
  <label>Hasta</label>
  <input type="date" name="hasta" value="{{ $hasta }}">
  <button type="submit">Aplicar</button>
</form>

<div class="grid" style="display:grid; gap:16px; grid-template-columns: repeat(4, 1fr);">
  <div class="card"><h3>Tiendas</h3><div class="big">{{ $kpis['stores'] }}</div></div>
  <div class="card"><h3>Bodegas</h3><div class="big">{{ $kpis['warehouses'] }}</div></div>
  <div class="card"><h3>Productos</h3><div class="big">{{ $kpis['products'] }}</div></div>
  <div class="card"><h3>Clientes</h3><div class="big">{{ $kpis['customers'] }}</div></div>
</div>

<div class="grid" style="display:grid; gap:16px; grid-template-columns: repeat(4, 1fr); margin-top:16px;">
  <div class="card">
    <h3>Ventas ({{ $desde }} → {{ $hasta }})</h3>
    <p>Docs: <b>{{ $ventas->cnt ?? 0 }}</b></p>
    <p>Subtotal: <b>{{ number_format($ventas->subtotal ?? 0,2) }}</b></p>
    <p>IVA: <b>{{ number_format($ventas->tax ?? 0,2) }}</b></p>
    <p>Total: <b>{{ number_format($ventas->total ?? 0,2) }}</b></p>
  </div>
  <div class="card">
    <h3>Compras ({{ $desde }} → {{ $hasta }})</h3>
    <p>Docs: <b>{{ $compras->cnt ?? 0 }}</b></p>
    <p>Subtotal: <b>{{ number_format($compras->subtotal ?? 0,2) }}</b></p>
    <p>IVA: <b>{{ number_format($compras->tax ?? 0,2) }}</b></p>
    <p>Total: <b>{{ number_format($compras->total ?? 0,2) }}</b></p>
  </div>
  <div class="card">
    <h3>Inventario</h3>
    <p>Unidades: <b>{{ number_format($inventario->unidades ?? 0,3) }}</b></p>
    <p>Valor: <b>{{ number_format($inventario->valor ?? 0,2) }}</b></p>
  </div>
  <div class="card">
    <h3>Caja y Bancos</h3>
    <p>Caja: <b>{{ number_format($saldoCaja,2) }}</b></p>
    <p>Bancos: <b>{{ number_format($saldoBcos,2) }}</b></p>
  </div>
</div>

<div class="grid" style="display:grid; gap:16px; grid-template-columns: 1fr 1fr; margin-top:16px;">
  <div class="card">
    <h3>Últimos asientos</h3>
    <table>
      <tr><th>ID</th><th>Fecha</th><th>Desc</th><th>Debe</th><th>Haber</th><th>OK</th></tr>
      @forelse($asientos as $e)
      <tr>
        <td>#{{ $e->id }}</td>
        <td>{{ $e->date }}</td>
        <td>{{ $e->description }}</td>
        <td>{{ number_format($e->debe,2) }}</td>
        <td>{{ number_format($e->haber,2) }}</td>
        <td>@if(round($e->debe,2)===round($e->haber,2)) ✔ @else ✘ @endif</td>
      </tr>
      @empty
      <tr><td colspan="6">Sin asientos.</td></tr>
      @endforelse
    </table>
  </div>

  <div class="card">
    <h3>Top stock</h3>
    <table>
      <tr><th>SKU</th><th>Producto</th><th>Bodega</th><th>Qty</th></tr>
      @foreach($stockTop as $r)
        <tr><td>{{ $r->sku }}</td><td>{{ $r->name }}</td><td>{{ $r->warehouse }}</td><td>{{ rtrim(rtrim(number_format($r->qty,3,'.',''), '0'), '.') }}</td></tr>
      @endforeach
    </table>
  </div>
</div>

<div class="grid" style="display:grid; gap:16px; grid-template-columns: 1fr 1fr; margin-top:16px;">
  <div class="card">
    <h3>Top vendidos ({{ $desde }} → {{ $hasta }})</h3>
    <table>
      <tr><th>SKU</th><th>Producto</th><th>Qty</th><th>Monto</th></tr>
      @forelse($topVendidos as $r)
      <tr><td>{{ $r->sku }}</td><td>{{ $r->name }}</td><td>{{ rtrim(rtrim(number_format($r->qty,3,'.',''), '0'), '.') }}</td><td>{{ number_format($r->monto,2) }}</td></tr>
      @empty
      <tr><td colspan="4">Sin ventas en el periodo.</td></tr>
      @endforelse
    </table>
  </div>

  <div class="card">
    <h3>Últimos movimientos de Kardex</h3>
    <table>
      <tr><th>Fecha</th><th>SKU</th><th>Bodega</th><th>Tipo</th><th>Qty</th><th>Costo</th><th>Ref</th></tr>
      @forelse($kardex as $k)
      <tr>
        <td>{{ $k->occurred_at }}</td>
        <td>{{ $k->sku }}</td>
        <td>{{ $k->warehouse }}</td>
        <td>{{ $k->movement_type }}</td>
        <td>{{ rtrim(rtrim(number_format($k->qty,3,'.',''), '0'), '.') }}</td>
        <td>{{ number_format($k->unit_cost,2) }}</td>
        <td>{{ $k->ref_type }}:{{ $k->ref_id }}</td>
      </tr>
      @empty
      <tr><td colspan="7">Sin movimientos.</td></tr>
      @endforelse
    </table>
  </div>
</div>

<style>
  .card{background:#121212; padding:16px; border-radius:12px; box-shadow:0 0 0 1px #222 inset}
  .big{font-size:28px; font-weight:700; margin-top:6px}
</style>

@endsection

