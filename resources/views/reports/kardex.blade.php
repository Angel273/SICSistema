@extends('layout')

@section('title', 'Kardex por Producto')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  {{-- Header + Acciones --}}
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold">Kardex por Producto</h1>
      <p class="text-sm text-gray-500">Rango y filtros aplicados se conservan en las exportaciones.</p>
    </div>

    <div class="flex items-center gap-2">
      <a
        href="{{ route('exports.kardex.csv', request()->query()) }}"
        class="px-3 py-2 rounded-lg border text-sm hover:bg-gray-50"
        title="Exportar CSV"
      >⬇️ CSV</a>

      <a
        href="{{ route('exports.kardex.pdf', request()->query()) }}"
        class="px-3 py-2 rounded-lg bg-black text-white text-sm hover:opacity-90"
        title="Exportar PDF"
      >🧾 PDF</a>
    </div>
  </div>

  {{-- Filtros --}}
  <form method="GET" class="grid md:grid-cols-5 gap-3 p-4 border rounded-xl bg-white">
    <div class="md:col-span-2">
      <label class="block text-xs text-gray-500 mb-1">Producto</label>
      <select name="product_id" required class="w-full rounded-lg border px-3 py-2">
        <option value="">-- Selecciona --</option>
        @foreach($products as $p)
          <option value="{{ $p->id }}" {{ (string)$pid===(string)$p->id ? 'selected' : '' }}>
            {{ $p->sku }} — {{ $p->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-xs text-gray-500 mb-1">Bodega</label>
      <select name="warehouse_id" class="w-full rounded-lg border px-3 py-2">
        <option value="">Todas</option>
        @foreach($warehouses as $w)
          <option value="{{ $w->id }}" {{ (string)$wh===(string)$w->id ? 'selected' : '' }}>
            {{ $w->code }} — {{ $w->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-xs text-gray-500 mb-1">Desde</label>
      <input type="date" name="desde" value="{{ $desde }}" class="w-full rounded-lg border px-3 py-2">
    </div>

    <div>
      <label class="block text-xs text-gray-500 mb-1">Hasta</label>
      <input type="date" name="hasta" value="{{ $hasta }}" class="w-full rounded-lg border px-3 py-2">
    </div>

    <div class="md:col-span-5 flex items-center justify-end gap-2">
      <a href="{{ route('reports.kardex') }}" class="px-3 py-2 rounded-lg border text-sm">Limpiar</a>
      <button class="px-4 py-2 rounded-lg bg-black text-white text-sm">Aplicar</button>
    </div>
  </form>

  {{-- Contenido --}}
  @if(!$pid)
    <div class="rounded-xl border bg-white p-6 text-gray-500 text-center">
      Elegí un producto para ver su Kardex.
    </div>
  @else
    {{-- Resumen inicial --}}
    <div class="rounded-xl border bg-white p-4">
      <div class="font-semibold">{{ $prod->sku }} — {{ $prod->name }}</div>
      <div class="text-sm text-gray-600 mt-1">
        <b>Saldo inicial:</b>
        Qty {{ rtrim(rtrim(number_format($opening['qty'],3,'.',''), '0'), '.') }},
        Valor {{ number_format($opening['value'],2) }}
      </div>
    </div>

    {{-- Tabla --}}
    <div class="rounded-xl border overflow-hidden bg-white">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr class="text-left">
              <th class="px-4 py-3">Fecha</th>
              <th class="px-4 py-3">Bodega</th>
              <th class="px-4 py-3">Tipo</th>
              <th class="px-4 py-3 text-right">Cantidad</th>
              <th class="px-4 py-3 text-right">Costo Unit.</th>
              <th class="px-4 py-3 text-right">Valor</th>
              <th class="px-4 py-3">Referencia</th>
              <th class="px-4 py-3 text-right">Saldo Qty</th>
              <th class="px-4 py-3 text-right">Saldo Valor</th>
            </tr>
          </thead>

          <tbody class="[&>tr:nth-child(even)]:bg-gray-50/40">
            @forelse($rows as $r)
              <tr>
                <td class="px-4 py-2 whitespace-nowrap">{{ $r->date }}</td>
                <td class="px-4 py-2">{{ $r->wh }}</td>
                <td class="px-4 py-2">{{ $r->type }}</td>
                <td class="px-4 py-2 text-right">
                  {{ rtrim(rtrim(number_format($r->qty,3,'.',''), '0'), '.') }}
                </td>
                <td class="px-4 py-2 text-right">{{ number_format($r->unit_cost,2) }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($r->value,2) }}</td>
                <td class="px-4 py-2">{{ $r->ref }}</td>
                <td class="px-4 py-2 text-right">
                  {{ rtrim(rtrim(number_format($r->bal_qty,3,'.',''), '0'), '.') }}
                </td>
                <td class="px-4 py-2 text-right font-medium">{{ number_format($r->bal_val,2) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                  Sin movimientos en el período.
                </td>
              </tr>
            @endforelse
          </tbody>

          <tfoot class="bg-gray-100 font-semibold">
            <tr>
              <td colspan="3" class="px-4 py-3 text-right">Totales período:</td>
              <td class="px-4 py-3 text-right">
                {{ rtrim(rtrim(number_format($totals['in'] - $totals['out'],3,'.',''), '0'), '.') }}
              </td>
              <td class="px-4 py-3"></td>
              <td class="px-4 py-3 text-right">
                {{ number_format($totals['value_in'] - $totals['value_out'],2) }}
              </td>
              <td class="px-4 py-3"></td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  @endif

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
