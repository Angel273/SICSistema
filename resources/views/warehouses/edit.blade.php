@extends('layout')
@section('content')
<h1>Editar bodega</h1>

@if ($errors->any())
  <div class="card" style="border-left:4px solid #e11d48; background:#fff5f5; padding:12px;">
    <strong>Corrige estos campos:</strong>
    <ul style="margin:6px 0 0 18px;">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('warehouses.update',$warehouse->id) }}" class="card" style="display:grid; gap:12px; max-width:720px;">
  @csrf @method('PUT')

  <label><span>Tienda</span>
    <select name="store_id" required>
      @foreach($stores as $s)
        <option value="{{ $s->id }}" @selected(old('store_id',$warehouse->store_id)==$s->id)>
          {{ $s->code ? "$s->code — " : "" }}{{ $s->name }}
        </option>
      @endforeach
    </select>
  </label>

  <label><span>Código</span>
    <input name="code" value="{{ old('code',$warehouse->code) }}" maxlength="20" required>
  </label>

  <label><span>Nombre</span>
    <input name="name" value="{{ old('name',$warehouse->name) }}" maxlength="100" required>
  </label>

  <label><span>Dirección</span>
    <input name="address" value="{{ old('address',$warehouse->address) }}">
  </label>

  <div style="display:flex; gap:8px; margin-top:4px;">
    <button class="btn">Actualizar</button>
    <a href="{{ route('warehouses.index') }}" class="btn">Volver</a>
  </div>
</form>
@endsection
