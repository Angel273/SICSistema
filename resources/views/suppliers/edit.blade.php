@extends('layout')
@section('content')
<h2>Editar proveedor #{{ $supplier->id }}</h2>
<form method="POST" action="{{ route('suppliers.update',$supplier->id) }}">
  @csrf @method('PUT')
  <label>Nombre</label><br>
  <input type="text" name="name" value="{{ old('name',$supplier->name) }}" required><br>

  <label>Email</label><br>
  <input type="email" name="email" value="{{ old('email',$supplier->email) }}"><br>

  <label>Teléfono</label><br>
  <input type="text" name="phone" value="{{ old('phone',$supplier->phone) }}"><br>

  <label>NIT</label><br>
  <input type="text" name="tax_id" value="{{ old('tax_id',$supplier->tax_id) }}"><br>

  <label>Dirección</label><br>
  <input type="text" name="address" value="{{ old('address',$supplier->address) }}"><br><br>

  <button type="submit">Guardar cambios</button>
</form>
@endsection

