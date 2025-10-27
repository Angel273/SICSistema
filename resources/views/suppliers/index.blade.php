@extends('layout')
@section('content')
<h2>Proveedores</h2>
<a href="{{ route('suppliers.create') }}">➕ Nuevo proveedor</a>

<table>
  <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>NIT</th><th>Dirección</th><th>Acciones</th></tr>
  @forelse($rows as $r)
    <tr>
      <td>{{ $r->id }}</td>
      <td>{{ $r->name }}</td>
      <td>{{ $r->email }}</td>
      <td>{{ $r->phone }}</td>
      <td>{{ $r->tax_id }}</td>
      <td>{{ $r->address }}</td>
      <td>
        <a href="{{ route('suppliers.edit',$r->id) }}">✏️ Editar</a>
        <form action="{{ route('suppliers.destroy',$r->id) }}" method="POST" style="display:inline">
          @csrf @method('DELETE')
          <button type="submit" onclick="return confirm('¿Eliminar proveedor?')">🗑 Eliminar</button>
        </form>
      </td>
    </tr>
  @empty
    <tr><td colspan="7">No hay proveedores.</td></tr>
  @endforelse
</table>
{{ $rows->links() }}
@endsection
