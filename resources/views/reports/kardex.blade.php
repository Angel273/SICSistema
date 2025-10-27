@extends('layout')
@section('content')
<h2>Kardex por producto</h2>

<form method="GET" action="{{ route('reports.kardex') }}" style="margin:10px 0;">
  <label>Producto</label>
  <select name="product_id" required>
    <option value="">-- Selecciona --</option>
    @foreach($products as $p)
      <option value="{{ $p->id }}" {{ (string)$pid===(string)$p->id ? 'selected' : '' }}>
        {{ $p->sku }} - {{ $p->name }}
      </option>
    @endforeach
  </select>

  <label style="margin-left:8px;">Bodega</label>
  <select name="warehouse_id">
    <option value="">Todas</option>
    @foreach($warehouses as $w)
      <option value="{{ $w->id }}" {{ (string)$wh===(string)$w->id ? 'selected' : '' }}>
        {{ $w->code }} - {{ $w->name }}
      </option>
    @endforeach
  </select>

  <label style="margin-left:8px;">Desde</label>
  <input type="date" name="desde" value="{{ $desde }}">
  <label>Hasta</label>
  <input type="date" name="hasta" value="{{ $hasta }}">
  <button type="submit">Filtrar</button>
</form>

@if(!$pid)
  <p>Elegí un producto para ver su kardex.</p>
@else
  <div style="margin:8px 0 14px;">
    <b>{{ $prod->sku }} - {{ $prod->name }}</b><br>
    <b>Saldo inicial:</b> Qty {{ rtrim(rtrim(number_format($opening['qty'],3,'.',''), '0'), '.') }},
    Valor {{ number_format($opening['value'],2) }}
  </div>

  <table>
    <tr>
      <th>Fecha</th>
      <th>Bodega</th>
      <th>Tipo</th>
      <th>Qty</th>
      <th>Costo</th>
      <th>Valor</th>
      <th>Ref</th>
      <th>Saldo Qty</th>
      <th>Saldo Valor</th>
    </tr>
    @forelse($rows as $r)
      <tr>
        <td>{{ $r->date }}</td>
        <td>{{ $r->wh }}</td>
        <td>{{ $r->type }}</td>
        <td>{{ rtrim(rtrim(number_format($r->qty,3,'.',''), '0'), '.') }}</td>
        <td>{{ number_format($r->unit_cost,2) }}</td>
        <td>{{ number_format($r->value,2) }}</td>
        <td>{{ $r->ref }}</td>
        <td>{{ rtrim(rtrim(number_format($r->bal_qty,3,'.',''), '0'), '.') }}</td>
        <td>{{ number_format($r->bal_val,2) }}</td>
      </tr>
    @empty
      <tr><td colspan="9">Sin movimientos en el período.</td></tr>
    @endforelse

    <tr style="background:#141414;font-weight:700;">
      <td colspan="3" style="text-align:right;">Totales período:</td>
      <td>{{ rtrim(rtrim(number_format($totals['in'] - $totals['out'],3,'.',''), '0'), '.') }}</td>
      <td></td>
      <td>{{ number_format($totals['value_in'] - $totals['value_out'],2) }}</td>
      <td></td>
      <td colspan="2"></td>
    </tr>
  </table>
@endif
@endsection
