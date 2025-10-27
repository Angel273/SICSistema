@extends('layout')
@section('content')
<h2>Nueva tienda</h2>
<form method="POST" action="{{ route('stores.store') }}">
  @csrf
  <label>Código</label><br>
  <input type="text" name="code" value="{{ old('code') }}" required><br>

  <label>Nombre</label><br>
  <input type="text" name="name" value="{{ old('name') }}" required><br>

  <label>Dirección</label><br>
  <input type="text" name="address" value="{{ old('address') }}"><br><br>

  <button type="submit">Guardar</button>
</form>
@endsection
