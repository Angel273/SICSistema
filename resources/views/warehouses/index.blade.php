@extends('layout')

@section('content')
<h1>Bodegas</h1>

<a href="{{ route('warehouses.create') }}" class="btn">+ Nueva bodega</a>

<div class="card" style="margin-top:12px;">
  <table class="table">
    <thead>
      <tr>
        <th style="width:80px;">ID</th>
        <th style="width:140px;">Código</th>
        <th>Nombre</th>
        <th>Tienda</th>
        <th>Dirección</th>
        <th style="text-align:right; width:220px;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td><code>{{ $r->code }}</code></td>
          <td>{{ $r->name }}</td>
          <td>{{ $r->store_code ? $r->store_code.' — ' : '' }}{{ $r->store_name }}</td>
          <td>{{ $r->address }}</td>
          <td style="text-align:right;">
            <a class="btn" href="{{ route('warehouses.edit',$r->id) }}">Editar</a>
            <form method="POST" action="{{ route('warehouses.destroy',$r->id) }}" style="display:inline;">
              @csrf @method('DELETE')
              <button class="btn btnDel" onclick="return confirm('¿Eliminar bodega {{ $r->name }}?')">Eliminar</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" style="opacity:.7;">No hay bodegas registradas.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div style="margin-top:12px;">
  {{ $rows->links() }}
</div>
@endsection
