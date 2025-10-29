@extends('layout')

@section('title','Dashboard')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
@endsection

@section('page_title','Dashboard')

@section('page_actions')
  <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
    <div class="form-check me-2">
      <input class="form-check-input" type="checkbox" disabled checked>
      <label class="form-check-label">Desde</label>
    </div>
    <input type="date" name="desde" value="{{ $desde }}" class="form-control form-control-sm" style="width:150px">
    <div class="form-check ms-2 me-2">
      <input class="form-check-input" type="checkbox" disabled checked>
      <label class="form-check-label">Hasta</label>
    </div>
    <input type="date" name="hasta" value="{{ $hasta }}" class="form-control form-control-sm" style="width:150px">
    <button type="submit" class="btn btn-brand btn-sm"><i class="bi bi-funnel"></i> Aplicar</button>
  </form>
@endsection

@section('content')

{{-- KPIs superiores --}}
<div class="row g-3">
  <div class="col-6 col-md-3">
    <div class="card p-3 h-100">
      <div class="text-muted small">Tiendas</div>
      <div class="fs-3 fw-bold mt-1">{{ $kpis['stores'] }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card p-3 h-100">
      <div class="text-muted small">Bodegas</div>
      <div class="fs-3 fw-bold mt-1">{{ $kpis['warehouses'] }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card p-3 h-100">
      <div class="text-muted small">Productos</div>
      <div class="fs-3 fw-bold mt-1">{{ $kpis['products'] }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card p-3 h-100">
      <div class="text-muted small">Clientes</div>
      <div class="fs-3 fw-bold mt-1">{{ $kpis['customers'] }}</div>
    </div>
  </div>
</div>

{{-- Resumen Ventas/Compras/Inventario/Caja --}}
<div class="row g-3 mt-1">
  <div class="col-lg-3">
    <div class="card p-3 h-100">
      <div class="fw-semibold">Ventas ({{ $desde }} → {{ $hasta }})</div>
      <div class="mt-2 small text-muted">Docs: <b>{{ $ventas->cnt ?? 0 }}</b></div>
      <div class="d-flex justify-content-between mt-1"><span>Subtotal</span><b>${{ number_format($ventas->subtotal ?? 0,2) }}</b></div>
      <div class="d-flex justify-content-between mt-1"><span>IVA</span><b>${{ number_format($ventas->tax ?? 0,2) }}</b></div>
      <div class="separator"></div>
      <div class="d-flex justify-content-between"><span>Total</span><b class="text-primary">${{ number_format($ventas->total ?? 0,2) }}</b></div>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="card p-3 h-100">
      <div class="fw-semibold">Compras ({{ $desde }} → {{ $hasta }})</div>
      <div class="mt-2 small text-muted">Docs: <b>{{ $compras->cnt ?? 0 }}</b></div>
      <div class="d-flex justify-content-between mt-1"><span>Subtotal</span><b>${{ number_format($compras->subtotal ?? 0,2) }}</b></div>
      <div class="d-flex justify-content-between mt-1"><span>IVA</span><b>${{ number_format($compras->tax ?? 0,2) }}</b></div>
      <div class="separator"></div>
      <div class="d-flex justify-content-between"><span>Total</span><b class="text-primary">${{ number_format($compras->total ?? 0,2) }}</b></div>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="card p-3 h-100">
      <div class="fw-semibold">Inventario</div>
      <div class="d-flex justify-content-between mt-2"><span>Unidades</span><b>{{ number_format($inventario->unidades ?? 0,3) }}</b></div>
      <div class="separator"></div>
      <div class="d-flex justify-content-between"><span>Valor</span><b class="text-primary">${{ number_format($inventario->valor ?? 0,2) }}</b></div>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="card p-3 h-100">
      <div class="fw-semibold">Caja y Bancos</div>
      <div class="d-flex justify-content-between mt-2"><span>Caja</span><b>${{ number_format($saldoCaja,2) }}</b></div>
      <div class="d-flex justify-content-between mt-1"><span>Bancos</span><b>${{ number_format($saldoBcos,2) }}</b></div>
    </div>
  </div>
</div>

{{-- Gráfico Ventas vs Compras --}}
@php
  // Espera colecciones/arrays de pares [date => total] o registros {date,total}
  $ventasSeries   = $ventasSeries   ?? collect();
  $comprasSeries  = $comprasSeries  ?? collect();

  // Normaliza a arrays simples para Chart.js
  $labels = collect($ventasSeries)->pluck('date')
            ->merge(collect($comprasSeries)->pluck('date'))
            ->unique()->sort()->values(); // días del rango

  $ventasData  = $labels->map(fn($d) => (float)(collect($ventasSeries)->firstWhere('date',$d)['total'] ?? collect($ventasSeries)->firstWhere('date',$d)->total ?? 0));
  $comprasData = $labels->map(fn($d) => (float)(collect($comprasSeries)->firstWhere('date',$d)['total'] ?? collect($comprasSeries)->firstWhere('date',$d)->total ?? 0));
@endphp

@if($labels->count() > 0)
<div class="card mt-3">
  <div class="card-header fw-semibold">Ventas vs Compras por día</div>
  <div class="card-body">
    <canvas id="vcChart" height="72"></canvas>
  </div>
</div>
@endif

<div class="row g-3 mt-1">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header fw-semibold d-flex justify-content-between">
        <span>Últimos asientos</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead>
              <tr><th>ID</th><th>Fecha</th><th>Desc</th><th class="text-end">Debe</th><th class="text-end">Haber</th><th>OK</th></tr>
            </thead>
            <tbody>
              @forelse($asientos as $e)
              <tr>
                <td class="text-muted">#{{ $e->id }}</td>
                <td>{{ $e->date }}</td>
                <td class="text-truncate" style="max-width:240px">{{ $e->description }}</td>
                <td class="text-end">${{ number_format($e->debe,2) }}</td>
                <td class="text-end">${{ number_format($e->haber,2) }}</td>
                <td>
                  @if(round($e->debe,2)===round($e->haber,2))
                    <span class="badge badge-success">OK</span>
                  @else
                    <span class="badge badge-danger">Descuadre</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="6" class="text-center text-muted py-4">Sin asientos.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header fw-semibold">Top stock</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead><tr><th>SKU</th><th>Producto</th><th>Bodega</th><th class="text-end">Qty</th></tr></thead>
            <tbody>
              @forelse($stockTop as $r)
              <tr>
                <td class="text-muted">{{ $r->sku }}</td>
                <td class="text-truncate" style="max-width:220px">{{ $r->name }}</td>
                <td>{{ $r->warehouse }}</td>
                <td class="text-end">{{ rtrim(rtrim(number_format($r->qty,3,'.',''), '0'), '.') }}</td>
              </tr>
              @empty
              <tr><td colspan="4" class="text-center text-muted py-4">Sin datos.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header fw-semibold">Top vendidos ({{ $desde }} → {{ $hasta }})</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead><tr><th>SKU</th><th>Producto</th><th class="text-end">Qty</th><th class="text-end">Monto</th></tr></thead>
            <tbody>
              @forelse($topVendidos as $r)
              <tr>
                <td class="text-muted">{{ $r->sku }}</td>
                <td class="text-truncate" style="max-width:220px">{{ $r->name }}</td>
                <td class="text-end">{{ rtrim(rtrim(number_format($r->qty,3,'.',''), '0'), '.') }}</td>
                <td class="text-end">${{ number_format($r->monto,2) }}</td>
              </tr>
              @empty
              <tr><td colspan="4" class="text-center text-muted py-4">Sin ventas en el periodo.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header fw-semibold">Últimos movimientos de Kardex</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead>
              <tr><th>Fecha</th><th>SKU</th><th>Bodega</th><th>Tipo</th><th class="text-end">Qty</th><th class="text-end">Costo</th><th>Ref</th></tr>
            </thead>
            <tbody>
              @forelse($kardex as $k)
              <tr>
                <td>{{ $k->occurred_at }}</td>
                <td class="text-muted">{{ $k->sku }}</td>
                <td>{{ $k->warehouse }}</td>
                <td>{{ $k->movement_type }}</td>
                <td class="text-end">{{ rtrim(rtrim(number_format($k->qty,3,'.',''), '0'), '.') }}</td>
                <td class="text-end">${{ number_format($k->unit_cost,2) }}</td>
                <td class="text-muted">{{ $k->ref_type }}:{{ $k->ref_id }}</td>
              </tr>
              @empty
              <tr><td colspan="7" class="text-center text-muted py-4">Sin movimientos.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js (via CDN) --}}
@if($labels->count() > 0)
  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
      (function(){
        const ctx = document.getElementById('vcChart');
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: @json($labels),
            datasets: [
              {
                label: 'Ventas',
                data: @json($ventasData),
                tension: .3,
                borderWidth: 2,
                fill: false
              },
              {
                label: 'Compras',
                data: @json($comprasData),
                tension: .3,
                borderWidth: 2,
                fill: false
              }
            ]
          },
          options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
              legend: { display: true },
              tooltip: { callbacks: {
                label: (ctx) => `${ctx.dataset.label}: $${Number(ctx.parsed.y).toFixed(2)}`
              }}
            },
            scales: {
              y: { ticks: { callback: v => '$' + v } }
            }
          }
        });
      })();
    </script>
  @endpush
@endif

@endsection

