@extends('layout')
@section('content')
<h2>Bodegas</h2>
<a href="{{ route('warehouses.create') }}">➕ Nueva bodega</a>

<table>
  <tr><th>ID</th><th>Código</th><th>Nombre</th><th>Tienda</th><th>Dirección</th><th>Acciones</th></tr>
  @forelse($rows as $r)
    <tr>
      <td>{{ $r->id }}</td>
      <td>{{ $r->code }}</td>
      <td>{{ $r->name }}</td>
      <td>{{ $r->store_code }} - {{ $r->store_name }}</td>
      <td>{{ $r->address }}</td>
      <td>
        <a href="{{ route('warehouses.edit',$r->id) }}">✏️ Editar</a>
        <form action="{{ route('warehouses.destroy',$r->id) }}" method="POST" style="display:inline">
          @csrf @method('DELETE')
          <button type="submit" onclick="return confirm('¿Eliminar bodega?')">🗑 Eliminar</button>
        </form>
      </td>
    </tr>
  @empty
    <tr><td colspan="6">No hay bodegas.</td></tr>
  @endforelse
</table>
{{ $rows->links() }}
@endsection
