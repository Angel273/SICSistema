@extends('layout')
@section('content')
<h2>Tiendas</h2>
<a href="{{ route('stores.create') }}">➕ Nueva tienda</a>

<table>
  <tr><th>ID</th><th>Código</th><th>Nombre</th><th>Dirección</th><th>Acciones</th></tr>
  @forelse($rows as $r)
    <tr>
      <td>{{ $r->id }}</td>
      <td>{{ $r->code }}</td>
      <td>{{ $r->name }}</td>
      <td>{{ $r->address }}</td>
      <td>
        <a href="{{ route('stores.edit',$r->id) }}">✏️ Editar</a>
        <form action="{{ route('stores.destroy',$r->id) }}" method="POST" style="display:inline">
          @csrf @method('DELETE')
          <button type="submit" onclick="return confirm('¿Eliminar tienda?')">🗑 Eliminar</button>
        </form>
      </td>
    </tr>
  @empty
    <tr><td colspan="5">No hay tiendas.</td></tr>
  @endforelse
</table>
{{ $rows->links() }}
@endsection
