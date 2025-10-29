@extends('layout')

@section('content')
<h1>Tiendas</h1>

<div style="display:flex; gap:8px; align-items:center; margin-bottom:12px;">
  <a href="{{ route('stores.create') }}" class="btn">+ Nueva tienda</a>

  <form method="GET" action="{{ route('stores.index') }}" class="card" style="margin-left:auto; padding:8px 10px;">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por código o nombre…" />
    <button class="btn" style="margin-left:6px;">Buscar</button>
    @if(request('q'))
      <a class="btn" href="{{ route('stores.index') }}" style="margin-left:6px;">Limpiar</a>
    @endif
  </form>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th style="width:90px;">ID</th>
        <th style="width:160px;">Código</th>
        <th>Nombre</th>
        <th>Dirección</th>
        <th style="text-align:right; width:220px;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        @if(!request('q') || str_contains(Str::lower($r->code.' '.$r->name), Str::lower(request('q'))))
        <tr>
          <td>{{ $r->id }}</td>
          <td><code>{{ $r->code }}</code></td>
          <td>{{ $r->name }}</td>
          <td>{{ $r->address }}</td>
          <td style="text-align:right;">
            <a class="btn" href="{{ route('stores.edit',$r->id) }}">Editar</a>
            <form method="POST" action="{{ route('stores.destroy',$r->id) }}" style="display:inline;">
              @csrf @method('DELETE')
              <button class="btn btnDel" onclick="return confirm('¿Eliminar la tienda {{ $r->name }}?')">Eliminar</button>
            </form>
          </td>
        </tr>
        @endif
      @empty
        <tr><td colspan="5" style="opacity:.7;">No hay tiendas registradas.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Paginación --}}
<div style="margin-top:12px;">
  {{ $rows->withQueryString()->links() }}
</div>
@endsection
