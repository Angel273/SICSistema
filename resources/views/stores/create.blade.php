@extends('layout')

@section('content')
<h1>Nueva tienda</h1>

@if ($errors->any())
  <div class="card" style="border-left:4px solid #e11d48; background:#fff5f5; padding:12px;">
    <strong>Corrige estos campos:</strong>
    <ul style="margin:6px 0 0 18px;">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('stores.store') }}" class="card" style="display:grid; gap:12px; max-width:720px;">
  @csrf
  <label><span>Código</span>
    <input name="code" value="{{ old('code') }}" maxlength="20" required>
  </label>

  <label><span>Nombre</span>
    <input name="name" value="{{ old('name') }}" maxlength="100" required>
  </label>

  <label><span>Dirección (opcional)</span>
    <input name="address" value="{{ old('address') }}">
  </label>

  <div style="display:flex; gap:8px; margin-top:4px;">
    <button class="btn">Guardar</button>
    <a href="{{ url()->previous() ?: route('stores.index') }}" class="btn">Cancelar</a>
  </div>
</form>
@endsection
