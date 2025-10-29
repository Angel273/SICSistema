@extends('layout')

@section('content')
<h1>Editar tienda</h1>

@if ($errors->any())
  <div class="card" style="border-left:4px solid #e11d48; background:#fff5f5; padding:12px;">
    <strong>Corrige estos campos:</strong>
    <ul style="margin:6px 0 0 18px;">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('stores.update',$store->id) }}" class="card" style="display:grid; gap:12px; max-width:720px;">
  @csrf @method('PUT')

  <label><span>Código</span>
    <input name="code" value="{{ old('code',$store->code) }}" maxlength="20" required>
  </label>

  <label><span>Nombre</span>
    <input name="name" value="{{ old('name',$store->name) }}" maxlength="100" required>
  </label>

  <label><span>Dirección</span>
    <input name="address" value="{{ old('address',$store->address) }}">
  </label>

  <div style="display:flex; gap:8px; margin-top:4px;">
    <button class="btn">Actualizar</button>
    <a href="{{ route('stores.index') }}" class="btn">Volver</a>
  </div>
</form>
@endsection
