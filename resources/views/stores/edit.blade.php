@extends('layout')
@section('content')
<h2>Editar tienda #{{ $store->id }}</h2>
<form method="POST" action="{{ route('stores.update',$store->id) }}">
  @csrf @method('PUT')
  <label>Código</label><br>
  <input type="text" name="code" value="{{ old('code',$store->code) }}" required><br>

  <label>Nombre</label><br>
  <input type="text" name="name" value="{{ old('name',$store->name) }}" required><br>

  <label>Dirección</label><br>
  <input type="text" name="address" value="{{ old('address',$store->address) }}"><br><br>

  <button type="submit">Guardar cambios</button>
</form>
@endsection
