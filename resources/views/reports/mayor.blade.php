@extends('layout')
@section('content')
<h2>Libro Mayor</h2>

<form method="GET" action="{{ route('reports.mayor') }}" style="margin:10px 0;">
  <label>Cuenta</label>
  <select name="account" required>
    <option value="">-- Elegí una cuenta --</option>
    @foreach($accounts as $a)
      <option value="{{ $a->code }}" {{ (isset($code) && $code==$a->code) ? 'selected' : '' }}>
        {{ $a->code }} - {{ $a->name }}
      </option>
    @endforeach
  </select>

  <label style="margin-left:10px;">Desde</label>
  <input type="date" name="desde" value="{{ $desde }}">

  <label>Hasta</label>
  <input type="date" name="hasta" value="{{ $hasta }}">

  <button type="submit">Filtrar</button>
</form>

@if(!$acc)
  <p>Seleccioná una cuenta y el rango para ver el mayor.</p>
@else
  <div style="margin:8px 0 14px;">
    <b>Cuenta:</b> {{ $acc->code }} - {{ $acc->name }}<br>
    <b>Saldo inicial al {{ \Carbon\Carbon::parse($desde)->subDay()->format('Y-m-d') }}:</b>
    {{ number_format($opening,2) }}
  </div>

  <table>
    <tr>
      <th>Fecha</th>
      <th>Asiento</th>
      <th>Descripción</th>
      <th>Debe</th>
      <th>Haber</th>
      <th>Saldo</th>
    </tr>
    @forelse($rows as $r)
      <tr>
        <td>{{ $r->date }}</td>
        <td>#{{ $r->asiento }}</td>
        <td>{{ $r->description }}</td>
        <td>{{ $r->debit  ? number_format($r->debit ,2) : '' }}</td>
        <td>{{ $r->credit ? number_format($r->credit,2) : '' }}</td>
        <td>{{ number_format($r->balance,2) }}</td>
      </tr>
    @empty
      <tr><td colspan="6">Sin movimientos en el período.</td></tr>
    @endforelse

    <tr style="background:#141414; font-weight:700;">
      <td colspan="3" style="text-align:right;">Totales periodo:</td>
      <td>{{ number_format($totals['debe'],2) }}</td>
      <td>{{ number_format($totals['haber'],2) }}</td>
      <td>{{ number_format($totals['closing'],2) }}</td>
    </tr>
  </table>
@endif
@endsection
