@extends('layout')
@section('content')
<h2>Libro de Ventas</h2>

<form method="GET" action="{{ route('reports.ventas') }}" style="margin:10px 0;">
  <label>Desde</label>
  <input type="date" name="desde" value="{{ $desde }}">
  <label>Hasta</label>
  <input type="date" name="hasta" value="{{ $hasta }}">
  <label>Tienda</label>
  <select name="store_id">
    <option value="">Todas</option>
    @foreach($stores as $s)
      <option value="{{ $s->id }}" {{ (string)$store===(string)$s->id ? 'selected' : '' }}>
        {{ $s->code }} - {{ $s->name }}
      </option>
    @endforeach
  </select>
  <button type="submit">Filtrar</button>
</form>

<table>
  <tr>
    <th>Fecha</th>
    <th>Doc</th>
    <th>Cliente</th>
    <th>Gravado</th>
    <th>IVA</th>
    <th>Total</th>
  </tr>
  @forelse($rows as $r)
    <tr>
      <td>{{ $r->date }}</td>
      <td>VTA-{{ $r->id }}</td>
      <td>{{ $r->customer }}</td>
      <td>{{ number_format($r->subtotal,2) }}</td>
      <td>{{ number_format($r->tax,2) }}</td>
      <td>{{ number_format($r->total,2) }}</td>
    </tr>
  @empty
    <tr><td colspan="6">Sin ventas en el período.</td></tr>
  @endforelse

  <tr style="background:#141414; font-weight:700;">
    <td colspan="3" style="text-align:right;">Totales ({{ $tot->docs }} docs):</td>
    <td>{{ number_format($tot->gravado,2) }}</td>
    <td>{{ number_format($tot->iva,2) }}</td>
    <td>{{ number_format($tot->total,2) }}</td>
  </tr>
</table>
@endsection
