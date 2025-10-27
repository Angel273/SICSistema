@extends('layout')
@section('content')
<h2>Libro de Compras</h2>

<form method="GET" action="{{ route('reports.compras') }}" style="margin:10px 0;">
  <label>Desde</label>
  <input type="date" name="desde" value="{{ $desde }}">
  <label>Hasta</label>
  <input type="date" name="hasta" value="{{ $hasta }}">
  <label>Bodega</label>
  <select name="warehouse_id">
    <option value="">Todas</option>
    @foreach($warehouses as $w)
      <option value="{{ $w->id }}" {{ (string)$wh===(string)$w->id ? 'selected' : '' }}>
        {{ $w->code }} - {{ $w->name }}
      </option>
    @endforeach
  </select>
  <button type="submit">Filtrar</button>
</form>

<table>
  <tr>
    <th>Fecha</th>
    <th>Doc</th>
    <th>Proveedor</th>
    <th>Gravado</th>
    <th>IVA</th>
    <th>Total</th>
  </tr>
  @forelse($rows as $r)
    <tr>
      <td>{{ $r->date }}</td>
      <td>CPA-{{ $r->id }}</td>
      <td>{{ $r->supplier }}</td>
      <td>{{ number_format($r->subtotal,2) }}</td>
      <td>{{ number_format($r->tax,2) }}</td>
      <td>{{ number_format($r->total,2) }}</td>
    </tr>
  @empty
    <tr><td colspan="6">Sin compras en el período.</td></tr>
  @endforelse

  <tr style="background:#141414; font-weight:700;">
    <td colspan="3" style="text-align:right;">Totales ({{ $tot->docs }} docs):</td>
    <td>{{ number_format($tot->gravado,2) }}</td>
    <td>{{ number_format($tot->iva,2) }}</td>
    <td>{{ number_format($tot->total,2) }}</td>
  </tr>
</table>
@endsection
