{{-- resources/views/customers_master/index.blade.php --}}
@extends('layout')
@section('content')
<h1>Clientes (CxC)</h1>

<form method="GET" class="card" style="margin-bottom:12px;">
  <div style="display:flex; gap:8px; align-items:center;">
    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar cliente…">
    <button class="btn">Buscar</button>
    <a href="{{ route('customers.master.index') }}" class="btn">Limpiar</a>
  </div>
</form>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>Cliente</th>
        <th style="text-align:right;">Ventas crédito</th>
        <th style="text-align:right;">Cobros</th>
        <th style="text-align:right;">Saldo</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td>{{ $r->name }}</td>
          <td style="text-align:right;">${{ number_format($r->credit_total,2) }}</td>
          <td style="text-align:right;">${{ number_format($r->receipts_total,2) }}</td>
          <td style="text-align:right; font-weight:700;">${{ number_format($r->outstanding,2) }}</td>
          <td style="text-align:right;">
            <a class="btn" href="{{ route('customers.master.show',$r->id) }}">Ver detalle</a>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
