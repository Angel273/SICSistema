@extends('layout')
@section('content')
<h2>Cuentas por Pagar</h2>

<form method="GET" action="{{ route('reports.cxp') }}" style="margin:10px 0;">
  <label>Desde</label>
  <input type="date" name="desde" value="{{ $desde }}">
  <label>Hasta</label>
  <input type="date" name="hasta" value="{{ $hasta }}">
  <label>Proveedor</label>
  <select name="supplier_id">
    <option value="">Todos</option>
    @foreach($suppliers as $s)
      <option value="{{ $s->id }}" {{ (string)$supplier===(string)$s->id ? 'selected' : '' }}>
        {{ $s->name }}
      </option>
    @endforeach
  </select>
  <button type="submit">Filtrar</button>
</form>

<table>
  <tr>
    <th>Fecha</th>
    <th>Compra</th>
    <th>Proveedor</th>
    <th>Total</th>
    <th>Pagado</th>
    <th>Saldo</th>
  </tr>
  @forelse($rows as $r)
    <tr>
      <td>{{ $r->date }}</td>
      <td>CPA-{{ $r->id }}</td>
      <td>{{ $r->supplier }}</td>
      <td>{{ number_format($r->total,2) }}</td>
      <td>{{ number_format($r->pagado,2) }}</td>
      <td>{{ number_format($r->saldo,2) }}</td>
    </tr>
  @empty
    <tr><td colspan="6">Sin cuentas pendientes.</td></tr>
  @endforelse
  <tr style="background:#141414;font-weight:700;">
    <td colspan="3" style="text-align:right;">Totales:</td>
    <td>{{ number_format($tot['total'],2) }}</td>
    <td>{{ number_format($tot['pagado'],2) }}</td>
    <td>{{ number_format($tot['saldo'],2) }}</td>
  </tr>
</table>
@endsection
