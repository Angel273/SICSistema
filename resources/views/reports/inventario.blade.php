@extends('layout')

@section('title','Inventario Valorizado')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  {{-- Header + acciones --}}
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold">Inventario Valorizado</h1>
      <p class="text-sm text-gray-500">Filtros aplicados se mantienen en las exportaciones.</p>
    </div>

    <div class="flex items-center gap-2">
      <a
        href="{{ route('exports.inventario.csv', request()->query()) }}"
        class="px-3 py-2 rounded-lg border text-sm hover:bg-gray-50"
        title="Exportar CSV"
      >⬇️ CSV</a>

      <a
        href="{{ route('exports.inventario.pdf', request()->query()) }}"
        class="px-3 py-2 rounded-lg bg-black text-white text-sm hover:opacity-90"
        title="Exportar PDF"
      >🧾 PDF</a>
    </div>
  </div>

  {{-- Filtros --}}
  <form method="GET" class="grid md:grid-cols-4 gap-3 p-4 border rounded-xl bg-white">
    <div>
      <label class="block text-xs text-gray-500 mb-1">Bodega</label>
      <select name="warehouse_id" class="w-full rounded-lg border px-3 py-2">
        <option value="">Todas</option>
        @foreach($warehouses as $w)
          <option value="{{ $w->id }}" @selected((string)$wh === (string)$w->id)>
            {{ $w->code }} — {{ $w->name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="block text-xs text-gray-500 mb-1">Buscar</label>
      <input
        type="text"
        name="q"
        value="{{ $q }}"
        placeholder="SKU o nombre del producto"
        class="w-full rounded-lg border px-3 py-2"
      >
    </div>

    <div class="md:col-span-1 flex items-end justify-end gap-2">
      <a href="{{ route('reports.inventario') }}" class="px-3 py-2 rounded-lg border text-sm">Limpiar</a>
      <button class="px-4 py-2 rounded-lg bg-black text-white text-sm">Aplicar</button>
    </div>
  </form>

  {{-- Tabla principal --}}
  <div class="rounded-xl border overflow-hidden bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left">
            <th class="px-4 py-3">SKU</th>
            <th class="px-4 py-3">Producto</th>
            <th class="px-4 py-3">Bodega</th>
            <th class="px-4 py-3 text-right">Cantidad</th>
            <th class="px-4 py-3 text-right">Costo Prom.</th>
            <th class="px-4 py-3 text-right">Valor</th>
          </tr>
        </thead>
        <tbody class="[&>tr:nth-child(even)]:bg-gray-50/40">
          @forelse($rows as $r)
            <tr>
              <td class="px-4 py-2 whitespace-nowrap">{{ $r->sku }}</td>
              <td class="px-4 py-2">{{ $r->name }}</td>
              <td class="px-4 py-2">{{ $r->wh_code }}</td>
              <td class="px-4 py-2 text-right">
                {{ rtrim(rtrim(number_format($r->qty,3,'.',''), '0'), '.') }}
              </td>
              <td class="px-4 py-2 text-right">{{ number_format($r->avg_cost,2) }}</td>
              <td class="px-4 py-2 text-right font-medium">{{ number_format($r->value,2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                Sin existencias registradas.
              </td>
            </tr>
          @endforelse
        </tbody>
        <tfoot class="bg-gray-100 font-semibold">
          <tr>
            <td colspan="3" class="px-4 py-3 text-right">Totales:</td>
            <td class="px-4 py-3 text-right">
              {{ rtrim(rtrim(number_format($total->qty,3,'.',''), '0'), '.') }}
            </td>
            <td class="px-4 py-3"></td>
            <td class="px-4 py-3 text-right">{{ number_format($total->value,2) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- Totales por bodega --}}
  @if($byWh && count($byWh))
    <div class="rounded-xl border overflow-hidden bg-white">
      <div class="p-4 border-b bg-gray-50 font-semibold">Totales por bodega</div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr class="text-left">
              <th class="px-4 py-3">Bodega</th>
              <th class="px-4 py-3 text-right">Cantidad</th>
              <th class="px-4 py-3 text-right">Valor</th>
            </tr>
          </thead>
          <tbody class="[&>tr:nth-child(even)]:bg-gray-50/40">
            @foreach($byWh as $code => $b)
              <tr>
                <td class="px-4 py-2">{{ $code }} — {{ $b['wh_name'] }}</td>
                <td class="px-4 py-2 text-right">
                  {{ rtrim(rtrim(number_format($b['qty'],3,'.',''), '0'), '.') }}
                </td>
                <td class="px-4 py-2 text-right font-medium">
                  {{ number_format($b['value'],2) }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

</div>

{{-- Estilos mínimos para impresión --}}
<style>
@media print {
  a[href]:after { content: ''; }
  nav, form, .border-t, .pagination, .flex.items-center.justify-between { display:none !important; }
  table { font-size: 12px; }
  thead { position: sticky; top: 0; }
}
</style>
@endsection
