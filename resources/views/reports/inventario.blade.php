@extends('layout')
@section('content')
<h2>Inventario valorizado</h2>

<form method="GET" action="{{ route('reports.inventario') }}" style="margin:10px 0;">
  <label>Bodega</label>
  <select name="warehouse_id">
    <option value="">Todas</option>
    @foreach($warehouses as $w)
      <option value="{{ $w->id }}" {{ (string)$wh===(string)$w->id ? 'selected' : '' }}>
        {{ $w->code }} - {{ $w->name }}
      </option>
    @endforeach
  </select>
  <label style="margin-left:8px;">Buscar</label>
  <input type="text" name="q" value="{{ $q }}" placeholder="SKU o nombre">
  <button type="submit">Filtrar</button>
</form>

<table>
  <tr>
    <th>SKU</th>
    <th>Producto</th>
    <th>Bodega</th>
    <th>Qty</th>
    <th>Costo prom.</th>
    <th>Valor</th>
  </tr>
  @forelse($rows as $r)
    <tr>
      <td>{{ $r->sku }}</td>
      <td>{{ $r->name }}</td>
      <td>{{ $r->wh_code }}</td>
      <td>{{ rtrim(rtrim(number_format($r->qty,3,'.',''), '0'), '.') }}</td>
      <td>{{ number_format($r->avg_cost,2) }}</td>
      <td>{{ number_format($r->value,2) }}</td>
    </tr>
  @empty
    <tr><td colspan="6">Sin existencias.</td></tr>
  @endforelse

  <tr style="background:#141414;font-weight:700;">
    <td colspan="3" style="text-align:right;">Totales:</td>
    <td>{{ rtrim(rtrim(number_format($total->qty,3,'.',''), '0'), '.') }}</td>
    <td></td>
    <td>{{ number_format($total->value,2) }}</td>
  </tr>
</table>

@if($byWh && count($byWh))
  <h3 style="margin-top:16px;">Totales por bodega</h3>
  <table>
    <tr><th>Bodega</th><th>Qty</th><th>Valor</th></tr>
    @foreach($byWh as $code => $b)
      <tr>
        <td>{{ $code }} - {{ $b['wh_name'] }}</td>
        <td>{{ rtrim(rtrim(number_format($b['qty'],3,'.',''), '0'), '.') }}</td>
        <td>{{ number_format($b['value'],2) }}</td>
      </tr>
    @endforeach
  </table>
@endif
@endsection
