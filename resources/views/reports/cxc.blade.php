@extends('layout')

@section('title', 'Cuentas por Cobrar')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  {{-- Header + Acciones --}}
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold">Cuentas por Cobrar</h1>
      <p class="text-sm text-gray-500">Rango y filtros aplicados se conservan en las exportaciones.</p>
    </div>

    <div class="flex items-center gap-2">
      <a
        href="{{ route('exports.cxc.csv', request()->query()) }}"
        class="px-3 py-2 rounded-lg border text-sm hover:bg-gray-50"
        title="Exportar CSV"
      >⬇️ CSV</a>

      <a
        href="{{ route('exports.cxc.pdf', request()->query()) }}"
        class="px-3 py-2 rounded-lg bg-black text-white text-sm hover:opacity-90"
        title="Exportar PDF"
      >🧾 PDF</a>
    </div>
  </div>

  {{-- Filtros --}}
  <form method="GET" class="grid md:grid-cols-4 gap-3 p-4 border rounded-xl bg-white">
    <div>
      <label class="block text-xs text-gray-500 mb-1">Desde</label>
      <input type="date" name="desde" value="{{ $desde }}" class="w-full rounded-lg border px-3 py-2">
    </div>

    <div>
      <label class="block text-xs text-gray-500 mb-1">Hasta</label>
      <input type="date" name="hasta" value="{{ $hasta }}" class="w-full rounded-lg border px-3 py-2">
    </div>

    <div class="md:col-span-2">
      <label class="block text-xs text-gray-500 mb-1">Cliente</label>
      <select name="customer_id" class="w-full rounded-lg border px-3 py-2">
        <option value="">Todos</option>
        @foreach($customers as $c)
          <option value="{{ $c->id }}" {{ (string)$customer===(string)$c->id ? 'selected' : '' }}>
            {{ $c->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="md:col-span-4 flex items-center justify-end gap-2">
      <a href="{{ route('reports.cxc') }}" class="px-3 py-2 rounded-lg border text-sm">Limpiar</a>
      <button class="px-4 py-2 rounded-lg bg-black text-white text-sm">Aplicar</button>
    </div>
  </form>

  {{-- Tabla --}}
  <div class="rounded-xl border overflow-hidden bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left">
            <th class="px-4 py-3">Fecha</th>
            <th class="px-4 py-3">Venta</th>
            <th class="px-4 py-3">Cliente</th>
            <th class="px-4 py-3 text-right">Total</th>
            <th class="px-4 py-3 text-right">Pagado</th>
            <th class="px-4 py-3 text-right">Saldo</th>
          </tr>
        </thead>

        <tbody class="[&>tr:nth-child(even)]:bg-gray-50/40">
          @forelse($rows as $r)
            <tr>
              <td class="px-4 py-2 whitespace-nowrap">{{ $r->date }}</td>
              <td class="px-4 py-2">VTA-{{ $r->id }}</td>
              <td class="px-4 py-2">{{ $r->customer }}</td>
              <td class="px-4 py-2 text-right">{{ number_format($r->total,2) }}</td>
              <td class="px-4 py-2 text-right">{{ number_format($r->pagado,2) }}</td>
              <td class="px-4 py-2 text-right font-medium">{{ number_format($r->saldo,2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                Sin cuentas pendientes.
              </td>
            </tr>
          @endforelse
        </tbody>

        <tfoot class="bg-gray-100 font-semibold">
          <tr>
            <td colspan="3" class="px-4 py-3 text-right">Totales:</td>
            <td class="px-4 py-3 text-right">{{ number_format($tot['total'],2) }}</td>
            <td class="px-4 py-3 text-right">{{ number_format($tot['pagado'],2) }}</td>
            <td class="px-4 py-3 text-right">{{ number_format($tot['saldo'],2) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

{{-- Estilos para impresión --}}
<style>
@media print {
  a[href]:after { content: ''; }
  nav, form, .border-t, .pagination, .flex.items-center.justify-between { display:none !important; }
  table { font-size: 12px; }
  thead { position: sticky; top: 0; }
}
</style>
@endsection

