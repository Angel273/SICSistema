@extends('layout')  {{-- significa: usa el layout base --}}
@section('content') {{-- todo lo que está adentro se insertará en el @yield del layout --}}

<h2>Listado de productos</h2>
<a href="{{ route('products.create') }}">➕ Nuevo producto</a>

<table>
  <tr>
    <th>ID</th>
    <th>SKU</th>
    <th>Nombre</th>
    <th>Categoría</th>
    <th>Costo Promedio</th>
    <th>Acciones</th>
  </tr>

  @foreach($rows as $r)
  <tr>
    <td>{{ $r->id }}</td>
    <td>{{ $r->sku }}</td>
    <td>{{ $r->name }}</td>
    <td>{{ $r->category ?? '-' }}</td>
    <td>{{ number_format($r->avg_cost, 2) }}</td>
    <td>
      <a href="{{ route('products.edit', $r->id) }}">✏️ Editar</a>
      <form action="{{ route('products.destroy', $r->id) }}" method="POST" style="display:inline">
        @csrf
        @method('DELETE')
        <button type="submit" style="background:none;border:none;color:#ff7070;">🗑 Eliminar</button>
      </form>
    </td>
  </tr>
  @endforeach
</table>

{{-- Paginación automática de Laravel --}}
{{ $rows->links() }}

@endsection

