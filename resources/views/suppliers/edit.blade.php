@extends('layout')
@section('content')
<h1>Editar proveedor</h1>

@if ($errors->any())
  <div class="card" style="border-left:4px solid #e11d48; background:#fff5f5; padding:12px;">
    <strong>Corrige estos campos:</strong>
    <ul style="margin:6px 0 0 18px;">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('suppliers.update',$supplier->id) }}" class="card" style="display:grid; gap:12px; max-width:720px;">
  @csrf @method('PUT')

  <label><span>Nombre</span>
    <input name="name" value="{{ old('name',$supplier->name) }}" maxlength="120" required>
  </label>

  <label><span>Email</span>
    <input type="email" name="email" value="{{ old('email',$supplier->email) }}" maxlength="120">
  </label>

  <label><span>Teléfono</span>
    <input name="phone" value="{{ old('phone',$supplier->phone) }}" maxlength="40">
  </label>

  <label><span>Dirección</span>
    <input name="address" value="{{ old('address',$supplier->address) }}">
  </label>

  <label><span>NIT/NRC</span>
    <input name="tax_id" value="{{ old('tax_id',$supplier->tax_id) }}">
  </label>

  <div style="display:flex; gap:8px; margin-top:4px;">
    <button class="btn">Actualizar</button>
    <a href="{{ route('suppliers.index') }}" class="btn">Volver</a>
  </div>
</form>
@endsection
