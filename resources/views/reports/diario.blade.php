@extends('layout')
@section('content')
<h2>Libro Diario</h2>

<form method="GET" action="{{ route('reports.diario') }}" style="margin:10px 0;">
  <label>Desde</label>
  <input type="date" name="desde" value="{{ $desde }}">
  <label>Hasta</label>
  <input type="date" name="hasta" value="{{ $hasta }}">
  <button type="submit">Filtrar</button>
</form>

<table>
  <tr>
    <th>Fecha</th>
    <th>Asiento</th>
    <th>Descripción</th>
    <th>Cuenta</th>
    <th>Debe</th>
    <th>Haber</th>
  </tr>

  @php $current = null; @endphp
  @foreach($rows as $r)
    @if($current !== $r->asiento)
      @php
        $current = $r->asiento;
        $d = $balances[$current]['debe'] ?? 0;
        $h = $balances[$current]['haber'] ?? 0;
      @endphp
      <tr style="background:#141414">
        <td>{{ $r->date }}</td>
        <td>#{{ $r->asiento }}</td>
        <td colspan="4">{{ $r->description }}
          <span style="float:right; {{ round($d,2)==round($h,2) ? 'color:#6aff80;' : 'color:#ff7070;' }}">
            Total asiento: Debe {{ number_format($d,2) }} | Haber {{ number_format($h,2) }}
            {!! round($d,2)==round($h,2) ? '✔' : '✘' !!}
          </span>
        </td>
      </tr>
    @endif

    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td>{{ $r->code }} - {{ $r->account }}</td>
      <td>{{ $r->debit  ? number_format($r->debit ,2) : '' }}</td>
      <td>{{ $r->credit ? number_format($r->credit,2) : '' }}</td>
    </tr>
  @endforeach
</table>
@endsection
