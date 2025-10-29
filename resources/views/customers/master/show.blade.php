{{-- resources/views/customers_master/show.blade.php --}}
@extends('layout')
@section('content')
<h1>Cliente: {{ $customer->name }}</h1>

<div class="grid" style="grid-template-columns: 2fr 1fr; gap:16px;">
  <div style="display:grid; gap:16px;">
    <div class="card">
      <h3>Facturas a crédito abiertas</h3>
      <table class="table">
        <thead>
          <tr>
            <th>#</th><th>Fecha</th>
            <th style="text-align:right;">Total</th>
            <th style="text-align:right;">Pendiente</th>
            <th style="text-align:right;">Días</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($invoices as $i)
            <tr>
              <td>{{ $i->id }}</td>
              <td>{{ $i->date }}</td>
              <td style="text-align:right;">${{ number_format($i->total,2) }}</td>
              <td style="text-align:right; font-weight:700;">${{ number_format($i->outstanding,2) }}</td>
              <td style="text-align:right;">{{ $i->days }}</td>
              <td style="text-align:right;">
                <a class="btn" href="{{ route('receipts.create') }}?customer_id={{ $customer->id }}&sale_id={{ $i->id }}">Cobrar</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" style="opacity:.7;">Sin facturas abiertas.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card">
      <h3>Últimos cobros</h3>
      <table class="table">
        <thead><tr><th>#</th><th>Fecha</th><th>Venta</th><th style="text-align:right;">Monto</th></tr></thead>
        <tbody>
          @forelse($receipts as $r)
            <tr>
              <td>{{ $r->id }}</td>
              <td>{{ $r->date }}</td>
              <td>@if($r->sale_id)#{{ $r->sale_id }}@else—@endif</td>
              <td style="text-align:right;">${{ number_format($r->amount,2) }}</td>
            </tr>
          @empty
            <tr><td colspan="4" style="opacity:.7;">Sin cobros registrados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <h3>Resumen CxC</h3>
    <div style="display:grid; gap:8px;">
      <div style="display:flex; justify-content:space-between;">
        <span>Ventas a crédito:</span><strong>${{ number_format($totCredit,2) }}</strong>
      </div>
      <div style="display:flex; justify-content:space-between;">
        <span>Cobros:</span><strong>${{ number_format($totReceipts,2) }}</strong>
      </div>
      <div style="display:flex; justify-content:space-between; font-size:1.2em;">
        <span>Saldo:</span><strong>${{ number_format($saldo,2) }}</strong>
      </div>
      <hr>
      <div><strong>Aging</strong></div>
      <div style="display:flex; justify-content:space-between;"><span>0-30</span><span>${{ number_format($aging['b0_30'],2) }}</span></div>
      <div style="display:flex; justify-content:space-between;"><span>31-60</span><span>${{ number_format($aging['b31_60'],2) }}</span></div>
      <div style="display:flex; justify-content:space-between;"><span>61-90</span><span>${{ number_format($aging['b61_90'],2) }}</span></div>
      <div style="display:flex; justify-content:space-between;"><span>&gt;90</span><span>${{ number_format($aging['b90p'],2) }}</span></div>
      <hr>
      <a class="btn" href="{{ route('receipts.create') }}?customer_id={{ $customer->id }}">Registrar cobro</a>
    </div>
  </div>
</div>
@endsection
