@extends('layout')
@section('content')

<h2>Editar producto #{{ $p->id }}</h2>
<form method="POST" action="{{ route('products.update', $p->id) }}">
  @csrf
  @method('PUT')

  <label>SKU:</label><br>
  <input type="text" name="sku" value="{{ $p->sku }}" required><br>

  <label>Nombre:</label><br>
  <input type="text" name="name" value="{{ $p->name }}" required><br>

  <label>Categoría:</label><br>
  <select name="category_id">
    <option value="">-- Sin categoría --</option>
    @foreach($cats as $c)
      <option value="{{ $c->id }}" {{ $p->category_id == $c->id ? 'selected' : '' }}>
        {{ $c->name }}
      </option>
    @endforeach
  </select><br>

  <label>Tiene número de serie:</label>
  <input type="checkbox" name="has_serial" value="1" {{ $p->has_serial ? 'checked' : '' }}><br>

  <label>Costo promedio:</label><br>
  <input type="number" step="0.01" name="avg_cost" value="{{ $p->avg_cost }}"><br><br>

  <button type="submit">Guardar cambios</button>
</form>

@endsection
