@extends('layout')

@section('content')
<div class="card">
  <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
    <div class="page-title"> Libro Diario</div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      {{-- Export respetando filtros actuales --}}
      <a class="btn btn-soft" href="{{ route('exports.diario.csv') }}?desde={{ $desde }}&hasta={{ $hasta }}">Exportar CSV</a>
      <a class="btn btn-brand" href="{{ route('exports.diario.pdf', [], false) }}?desde={{ $desde }}&hasta={{ $hasta }}">Exportar PDF</a>
    </div>
  </div>

  <div class="card-body" style="padding:16px;">
    <form method="GET" action="{{ route('reports.diario') }}" style="display:grid; grid-template-columns: repeat(6, minmax(0,1fr)); gap:12px; align-items:end;">
      <div style="grid-column: span 2;">
        <label><span>Desde</span>
          <input type="date" name="desde" value="{{ $desde }}">
        </label>
      </div>
      <div style="grid-column: span 2;">
        <label><span>Hasta</span>
          <input type="date" name="hasta" value="{{ $hasta }}">
        </label>
      </div>
      <div>
        <button class="btn" type="submit">Filtrar</button>
      </div>
      <div>
        <a class="btn btn-soft" href="{{ route('reports.diario') }}">Limpiar</a>
      </div>
    </form>

    <div class="separator"></div>

    @php
      $current = null;
      $sumDebe = 0; $sumHaber = 0;
    @endphp

    <table class="table">
      <thead>
        <tr>
          <th style="width:110px;">Fecha</th>
          <th style="width:95px;">Asiento</th>
          <th>Descripción</th>
          <th style="width:320px;">Cuenta</th>
          <th class="total" style="width:140px; text-align:right;">Debe</th>
          <th class="total" style="width:140px; text-align:right;">Haber</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          @if($current !== $r->asiento)
            @php
              $current = $r->asiento;
              $d = round($balances[$current]['debe'] ?? 0, 2);
              $h = round($balances[$current]['haber'] ?? 0, 2);
              $ok = $d === $h;
              $sumDebe += $d; $sumHaber += $h;
            @endphp
            <tr>
              <td><strong>{{ $r->date }}</strong></td>
              <td><strong>#{{ $r->asiento }}</strong></td>
              <td colspan="4" style="padding:10px 12px; background:var(--bg); border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                  <div style="font-weight:600; color:var(--ink);">{{ $r->description }}</div>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:.92rem; color:var(--muted);">Total asiento:</span>
                    <span class="badge-soft">Debe {{ number_format($d,2) }}</span>
                    <span class="badge-soft">Haber {{ number_format($h,2) }}</span>
                    @if($ok)
                      <span class="badge-success" title="Asiento balanceado">Balanceado</span>
                    @else
                      <span class="badge-danger" title="Asiento descuadrado">Descuadrado</span>
                    @endif
                  </div>
                </div>
              </td>
            </tr>
          @endif

          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $r->code }} — {{ $r->account }}</td>
            <td class="total">{{ $r->debit  ? number_format($r->debit ,2) : '' }}</td>
            <td class="total">{{ $r->credit ? number_format($r->credit,2) : '' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align:center; color:var(--muted); padding:18px;">
              No hay asientos en el rango seleccionado.
            </td>
          </tr>
        @endforelse
      </tbody>

      @if(count($rows) > 0)
      <tfoot>
        <tr>
          <th colspan="4" style="text-align:right;">Totales del periodo</th>
          <th class="total">{{ number_format($sumDebe,2) }}</th>
          <th class="total">{{ number_format($sumHaber,2) }}</th>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
@endsection
