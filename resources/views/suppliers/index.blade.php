@extends('layout')
@section('content')
<h1>Proveedores</h1>

<a href="{{ route('suppliers.create') }}" class="btn">+ Nuevo proveedor</a>

<div class="card" style="margin-top:12px;">
  <table class="table">
    <thead>
      <tr>
        <th style="width:80px;">ID</th>
        <th>Nombre</th>
        <th>Email</th>
        <th>Teléfono</th>
        <th>Dirección</th>
        <th style="text-align:right; width:220px;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ $r->name }}</td>
          <td>{{ $r->email }}</td>
          <td>{{ $r->phone }}</td>
          <td>{{ $r->address }}</td>
          <td style="text-align:right;">
            <a class="btn" href="{{ route('suppliers.edit',$r->id) }}">Editar</a>
            <form method="POST" action="{{ route('suppliers.destroy',$r->id) }}" style="display:inline;">
              @csrf @method('DELETE')
              <button class="btn btnDel" onclick="return confirm('¿Eliminar proveedor {{ $r->name }}?')">Eliminar</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" style="opacity:.7;">No hay proveedores registrados.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div style="margin-top:12px;">
  {{ $rows->links() }}
</div>
@endsection
