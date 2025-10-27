@extends('layout')
@section('content')
<h2>Nuevo proveedor</h2>
<form method="POST" action="{{ route('suppliers.store') }}">
  @csrf
  <label>Nombre</label><br>
  <input type="text" name="name" value="{{ old('name') }}" required><br>

  <label>Email</label><br>
  <input type="email" name="email" value="{{ old('email') }}"><br>

  <label>Teléfono</label><br>
  <input type="text" name="phone" value="{{ old('phone') }}"><br>

  <label>NIT</label><br>
  <input type="text" name="tax_id" value="{{ old('tax_id') }}"><br>

  <label>Dirección</label><br>
  <input type="text" name="address" value="{{ old('address') }}"><br><br>

  <button type="submit">Guardar</button>
</form>
@endsection

