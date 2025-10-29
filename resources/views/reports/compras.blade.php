@extends('layout')

@section('title','Reporte de Compras')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  {{-- Header + acciones --}}
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold">Libro de Compras</h1>
      <p class="text-sm text-gray-500">Rango y filtros aplicados se conservan en las exportaciones.</p>
    </div>

    <div class="flex items-center gap-2">
      <a
        href="{{ route('exports.compras.csv', request()->query()) }}"
        class="px-3 py-2 rounded-lg border text-sm hover:bg-gray-50"
        title="Exportar CSV"
      >⬇️ CSV</a>

      <a
        href="{{ route('exports.compras.pdf', request()->query()) }}"
        class="px-3 py-2 rounded-lg bg-black text-white text-sm hover:opacity-90"
        title="Exportar PDF"
      >🧾 PDF</a>
    </div>
  </div>

  {{-- Filtros --}}
  <form method="GET" class="grid md:grid-cols-5 gap-3 p-4 border rounded-xl bg-white">
    <div>
      <label class="block text-xs text-gray-500 mb-1">Desde</label>
      <input type="date" name="desde" value="{{ request('desde') }}" class="w-full rounded-lg border px-3 py-2">
    </div>
    <div>
      <label class="block text-xs text-gray-500 mb-1">Hasta</label>
      <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full rounded-lg border px-3 py-2">
    </div>
    <div>
      <label class="block text-xs text-gray-500 mb-1">Proveedor</label>
      <select name="supplier_id" class="w-full rounded-lg border px-3 py-2">
        <option value="">Todos</option>
        @foreach(($suppliers ?? []) as $s)
          <option value="{{ $s->id }}" @selected(request('supplier_id')==$s->id)>{{ $s->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-500 mb-1">Tienda</label>
      <select name="store_id" class="w-full rounded-lg border px-3 py-2">
        <option value="">Todas</option>
        @foreach(($stores ?? []) as $st)
          <option value="{{ $st->id }}" @selected(request('store_id')==$st->id)>{{ $st->code }} — {{ $st->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-500 mb-1">Bodega</label>
      <select name="warehouse_id" class="w-full rounded-lg border px-3 py-2">
        <option value="">Todas</option>
        @foreach(($warehouses ?? []) as $w)
          <option value="{{ $w->id }}" @selected(request('warehouse_id')==$w->id)>{{ $w->code }} — {{ $w->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="md:col-span-5 flex items-center justify-end gap-2">
      <a href="{{ route('reports.compras') }}" class="px-3 py-2 rounded-lg border text-sm">Limpiar</a>
      <button class="px-4 py-2 rounded-lg bg-black text-white text-sm">Aplicar</button>
    </div>
  </form>

  {{-- Tabla --}}
  <div class="rounded-xl border overflow-hidden bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 sticky top-0">
          <tr class="text-left">
            <th class="px-4 py-3">Fecha</th>
            <th class="px-4 py-3">Documento</th>
            <th class="px-4 py-3">Proveedor</th>
            <th class="px-4 py-3">Tienda/Bodega</th>
            <th class="px-4 py-3 text-right">Gravado</th>
            <th class="px-4 py-3 text-right">Desc.</th>
            <th class="px-4 py-3 text-right">IVA</th>
            <th class="px-4 py-3 text-right">Total</th>
          </tr>
        </thead>
        <tbody class="[&>tr:nth-child(even)]:bg-gray-50/40">
  @php
    $sum_gravado = 0;
    $sum_desc    = 0;
    $sum_iva     = 0;
    $sum_total   = 0;
  @endphp

  @forelse($rows as $r)
    @php
      $gravado = $r->subtotal ?? 0;
      $desc    = $r->discount ?? 0;
      $iva     = $r->tax ?? ($r->iva ?? 0);
      $total   = $r->total ?? ($gravado - $desc + $iva);

      $sum_gravado += $gravado;
      $sum_desc    += $desc;
      $sum_iva     += $iva;
      $sum_total   += $total;
    @endphp

    <tr>
      <td class="px-4 py-2 whitespace-nowrap">
        {{ \Illuminate\Support\Carbon::parse($r->date ?? $r->purchase_date)->format('Y-m-d') }}
      </td>
      <td class="px-4 py-2">{{ $r->document_no ?? $r->invoice_no ?? ('#'.$r->id) }}</td>
      <td class="px-4 py-2">{{ $r->supplier_name ?? $r->supplier ?? '' }}</td>
      <td class="px-4 py-2">
        {{ ($r->store_code ?? '').($r->store_name ? ' — '.$r->store_name : '') }}
        @if(!empty($r->warehouse_code) || !empty($r->warehouse_name))
          <div class="text-xs text-gray-500">{{ $r->warehouse_code ?? '' }} {{ $r->warehouse_name ?? '' }}</div>
        @endif
      </td>
      <td class="px-4 py-2 text-right">{{ number_format($gravado,2) }}</td>
      <td class="px-4 py-2 text-right">{{ number_format($desc,2) }}</td>
      <td class="px-4 py-2 text-right">{{ number_format($iva,2) }}</td>
      <td class="px-4 py-2 text-right font-medium">{{ number_format($total,2) }}</td>
    </tr>
  @empty
    <tr>
      <td colspan="8" class="px-4 py-6 text-center text-gray-500">
        Sin resultados para los filtros seleccionados.
      </td>
    </tr>
  @endforelse
</tbody>

<tfoot class="bg-gray-100 font-semibold">
  <tr>
    <td colspan="4" class="px-4 py-3 text-right">Totales</td>
    <td class="px-4 py-3 text-right">{{ number_format($sum_gravado,2) }}</td>
    <td class="px-4 py-3 text-right">{{ number_format($sum_desc,2) }}</td>
    <td class="px-4 py-3 text-right">{{ number_format($sum_iva,2) }}</td>
    <td class="px-4 py-3 text-right">{{ number_format($sum_total,2) }}</td>
  </tr>
</tfoot>

      </table>
    </div>

    {{-- Paginación --}}
    @if(method_exists($rows,'links'))
      <div class="px-4 py-3 border-t bg-white">
        {{ $rows->appends(request()->query())->links() }}
      </div>
    @endif
  </div>

</div>

{{-- Estilos mínimos para impresión rápida desde el navegador (opcional) --}}
<style>
@media print{
  a[href]:after{content:''}
  nav, form, .border-t, .pagination{ display:none !important; }
  table{ font-size:12px }
  thead{ position:sticky; top:0 }
}
</style>
@endsection
