@extends('layout')

@section('title', 'Catálogo de Cuentas')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between flex-wrap gap-3">
    <h1 class="text-2xl font-bold">Catálogo de Cuentas</h1>
    <a href="{{ route('accounts.create') }}"
       class="px-4 py-2 rounded-lg bg-black text-white text-sm hover:opacity-90">
       ＋ Nueva cuenta
    </a>
  </div>

  {{-- Mensaje de éxito --}}
  @if(session('ok'))
    <div class="rounded-lg bg-green-50 border border-green-200 text-green-700 p-4 text-sm">
      {{ session('ok') }}
    </div>
  @endif

  {{-- Filtro / búsqueda --}}
  <form method="GET" action="{{ route('accounts.index') }}"
        class="flex flex-wrap items-center gap-3 bg-white border rounded-xl p-4">
    <input
      type="text"
      name="q"
      value="{{ $q }}"
      placeholder="Buscar por código o nombre..."
      class="flex-1 rounded-lg border px-3 py-2 min-w-[260px] focus:ring-2 focus:ring-indigo-500 focus:outline-none"
    >
    <button
      type="submit"
      class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50"
    >Buscar</button>
    @if($q)
      <a href="{{ route('accounts.index') }}" class="text-sm text-gray-500 hover:text-black">Limpiar</a>
    @endif
  </form>

  {{-- Tabla --}}
  <div class="rounded-xl border overflow-hidden bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left">
            <th class="px-4 py-3 w-[120px]">Código</th>
            <th class="px-4 py-3">Nombre</th>
            <th class="px-4 py-3 w-[150px]">Tipo</th>
            <th class="px-4 py-3 w-[160px] text-center">Acciones</th>
          </tr>
        </thead>

        <tbody class="[&>tr:nth-child(even)]:bg-gray-50/40">
          @forelse($rows as $r)
            <tr>
              <td class="px-4 py-2 font-medium">{{ $r->code }}</td>
              <td class="px-4 py-2">{{ $r->name }}</td>
              <td class="px-4 py-2">{{ ucfirst($r->type) }}</td>
              <td class="px-4 py-2 text-center space-x-2">
                <a href="{{ route('accounts.edit',$r->id) }}"
                   class="inline-block px-3 py-1 rounded-lg border text-xs hover:bg-gray-50">✏️ Editar</a>

                <form method="POST"
                      action="{{ route('accounts.destroy',$r->id) }}"
                      class="inline"
                      onsubmit="return confirm('¿Eliminar {{ $r->code }} - {{ $r->name }}?');">
                  @csrf @method('DELETE')
                  <button type="submit"
                          class="inline-block px-3 py-1 rounded-lg border border-red-300 text-xs text-red-600 hover:bg-red-50">
                    🗑️ Eliminar
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin cuentas registradas.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Paginación --}}
    <div class="px-4 py-3 border-t bg-white">
      {{ $rows->links() }}
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

