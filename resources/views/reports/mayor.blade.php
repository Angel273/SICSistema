@extends('layout')

@section('content')
<div class="card">
  {{-- Header con título y acciones --}}
  <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
    <div class="page-title">Libro Mayor</div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      {{-- Export respetando filtros actuales --}}
      <a class="btn btn-soft"
         href="{{ route('exports.mayor.csv') }}?account={{ $code ?? '' }}&desde={{ $desde }}&hasta={{ $hasta }}">
        Exportar CSV
      </a>
      <a class="btn btn-brand"
         href="{{ route('exports.mayor.pdf', [], false) }}?account={{ $code ?? '' }}&desde={{ $desde }}&hasta={{ $hasta }}">
        Exportar PDF
      </a>
    </div>
  </div>

  <div class="card-body" style="padding:16px;">
    {{-- Filtros --}}
    <form method="GET" action="{{ route('reports.mayor') }}"
          style="display:grid; grid-template-columns: repeat(12, minmax(0,1fr)); gap:12px; align-items:end;">
      <div style="grid-column: span 6;">
        <label><span>Cuenta</span>
          <select name="account" required>
            <option value="">-- Elegí una cuenta --</option>
            @foreach($accounts as $a)
              <option value="{{ $a->code }}" {{ (isset($code) && $code==$a->code) ? 'selected' : '' }}>
                {{ $a->code }} — {{ $a->name }}
              </option>
            @endforeach
          </select>
        </label>
      </div>

      <div style="grid-column: span 3;">
        <label><span>Desde</span>
          <input type="date" name="desde" value="{{ $desde }}">
        </label>
      </div>

      <div style="grid-column: span 3;">
        <label><span>Hasta</span>
          <input type="date" name="hasta" value="{{ $hasta }}">
        </label>
      </div>

      <div style="grid-column: span 2;">
        <button class="btn" type="submit">Filtrar</button>
      </div>
      <div style="grid-column: span 2;">
        <a class="btn btn-soft" href="{{ route('reports.mayor') }}">Limpiar</a>
      </div>
    </form>

    <div class="separator"></div>

    @if(!$acc)
      <div class="empty-state">Seleccioná una cuenta y el rango para ver el mayor.</div>
    @else
      {{-- Encabezado de cuenta + saldo inicial --}}
      <div class="info-row" style="display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:space-between; margin-bottom:10px;">
        <div style="font-weight:600;">
          <span style="color:var(--muted)">Cuenta:</span>
          {{ $acc->code }} — {{ $acc->name }}
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
          @php $fechaIni = \Carbon\Carbon::parse($desde)->subDay()->format('Y-m-d'); @endphp
          <span class="badge-soft">Saldo inicial al {{ $fechaIni }}</span>
          <span class="badge-success">{{ number_format($opening,2) }}</span>
        </div>
      </div>

      {{-- Tabla de movimientos --}}
      <table class="table">
        <thead>
          <tr>
            <th style="width:120px;">Fecha</th>
            <th style="width:100px;">Asiento</th>
            <th>Descripción</th>
            <th style="width:140px; text-align:right;">Debe</th>
            <th style="width:140px; text-align:right;">Haber</th>
            <th style="width:160px; text-align:right;">Saldo</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $r)
            <tr>
              <td>{{ $r->date }}</td>
              <td>#{{ $r->asiento }}</td>
              <td>{{ $r->description }}</td>
              <td class="total">{{ $r->debit  ? number_format($r->debit ,2) : '' }}</td>
              <td class="total">{{ $r->credit ? number_format($r->credit,2) : '' }}</td>
              <td class="total">{{ number_format($r->balance,2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="text-align:center; color:var(--muted); padding:16px;">
                Sin movimientos en el período.
              </td>
            </tr>
          @endforelse
        </tbody>

        @if(!empty($rows))
        <tfoot>
          <tr>
            <th colspan="3" style="text-align:right;">Totales del período</th>
            <th class="total">{{ number_format($totals['debe'],2) }}</th>
            <th class="total">{{ number_format($totals['haber'],2) }}</th>
            <th class="total">{{ number_format($totals['closing'],2) }}</th>
          </tr>
        </tfoot>
        @endif
      </table>
    @endif
  </div>
</div>
@endsection
