@extends('layout')
@section('content')
<h2>Cuentas por Cobrar</h2>

<form method="GET" action="{{ route('reports.cxc') }}" style="margin:10px 0;">
  <label>Desde</label>
  <input type="date" name="desde" value="{{ $desde }}">
  <label>Hasta</label>
  <input type="date" name="hasta" value="{{ $hasta }}">
  <label>Cliente</label>
  <select name="customer_id">
    <option value="">Todos</option>
    @foreach($customers as $c)
      <option value="{{ $c->id }}" {{ (string)$customer===(string)$c->id ? 'selected' : '' }}>
        {{ $c->name }}
      </option>
    @endforeach
  </select>
  <button type="submit">Filtrar</button>
</form>

<table>
  <tr>
    <th>Fecha</th>
    <th>Venta</th>
    <th>Cliente</th>
    <th>Total</th>
    <th>Pagado</th>
    <th>Saldo</th>
  </tr>
  @forelse($rows as $r)
    <tr>
      <td>{{ $r->date }}</td>
      <td>VTA-{{ $r->id }}</td>
      <td>{{ $r->customer }}</td>
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
