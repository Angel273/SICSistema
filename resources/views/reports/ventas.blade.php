@extends('layout')

@section('content')
<div class="card">
  {{-- Header con título y acciones --}}
  <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
    <div class="page-title">Libro de Ventas</div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      {{-- Export respetando filtros actuales --}}
      <a class="btn btn-soft"
         href="{{ route('exports.ventas.csv') }}?desde={{ $desde }}&hasta={{ $hasta }}&store_id={{ $store ?? '' }}">
        Exportar CSV
      </a>
      <a class="btn btn-brand"
         href="{{ route('exports.ventas.pdf') }}?desde={{ $desde }}&hasta={{ $hasta }}&store_id={{ $store ?? '' }}">
        Exportar PDF
      </a>
    </div>
  </div>

  <div class="card-body" style="padding:16px;">
    {{-- Filtros --}}
    <form method="GET" action="{{ route('reports.ventas') }}"
          style="display:grid; grid-template-columns: repeat(10, minmax(0,1fr)); gap:12px; align-items:end;">
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
      <div style="grid-column: span 3;">
        <label><span>Tienda</span>
          <select name="store_id">
            <option value="">Todas</option>
            @foreach($stores as $s)
              <option value="{{ $s->id }}" {{ (string)$store===(string)$s->id ? 'selected' : '' }}>
                {{ $s->code }} — {{ $s->name }}
              </option>
            @endforeach
          </select>
        </label>
      </div>
      <div style="grid-column: span 1;">
        <button class="btn" type="submit">Filtrar</button>
      </div>
      <div style="grid-column: span 1;">
        <a class="btn btn-soft" href="{{ route('reports.ventas') }}">Limpiar</a>
      </div>
    </form>

    <div class="separator"></div>

    @if(count($rows) === 0)
      <div class="empty-state">Sin ventas en el período seleccionado.</div>
    @else
      {{-- Info del rango --}}
      <div class="info-row" style="display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:space-between; margin-bottom:10px;">
        <div style="font-weight:600; color:var(--muted);">
          Período del <b>{{ $desde }}</b> al <b>{{ $hasta }}</b>
          @if($store)
            — <span class="badge-soft">Tienda {{ $stores->firstWhere('id',$store)->name ?? '' }}</span>
          @endif
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
          <span class="badge-soft">Total docs: {{ $tot->docs }}</span>
          <span class="badge-success">Ventas totales: {{ number_format($tot->total,2) }}</span>
        </div>
      </div>

      {{-- Tabla --}}
      <table class="table">
        <thead>
          <tr>
            <th style="width:120px;">Fecha</th>
            <th style="width:120px;">Doc</th>
            <th>Cliente</th>
            <th style="width:140px; text-align:right;">Gravado</th>
            <th style="width:120px; text-align:right;">IVA</th>
            <th style="width:140px; text-align:right;">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $r)
            <tr>
              <td>{{ $r->date }}</td>
              <td>VTA-{{ $r->id }}</td>
              <td>{{ $r->customer }}</td>
              <td class="total">{{ number_format($r->subtotal,2) }}</td>
              <td class="total">{{ number_format($r->tax,2) }}</td>
              <td class="total">{{ number_format($r->total,2) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" style="text-align:right;">Totales ({{ $tot->docs }} docs)</th>
            <th class="total">{{ number_format($tot->gravado,2) }}</th>
            <th class="total">{{ number_format($tot->iva,2) }}</th>
            <th class="total">{{ number_format($tot->total,2) }}</th>
          </tr>
        </tfoot>
      </table>
    @endif
  </div>
</div>
@endsection

