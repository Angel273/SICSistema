@extends('layout')
@section('content')
<h2>Nueva bodega</h2>
<form method="POST" action="{{ route('warehouses.store') }}">
  @csrf
  <label>Tienda</label><br>
  <select name="store_id" required>
    @foreach($stores as $s)
      <option value="{{ $s->id }}" {{ old('store_id')==$s->id?'selected':'' }}>
        {{ $s->code }} - {{ $s->name }}
      </option>
    @endforeach
  </select><br>

  <label>Código</label><br>
  <input type="text" name="code" value="{{ old('code') }}" required><br>

  <label>Nombre</label><br>
  <input type="text" name="name" value="{{ old('name') }}" required><br>

  <label>Dirección</label><br>
  <input type="text" name="address" value="{{ old('address') }}"><br><br>

  <button type="submit">Guardar</button>
</form>
@endsection
