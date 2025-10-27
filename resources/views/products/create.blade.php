@extends('layout')
@section('content')

<h2>Nuevo producto</h2>
<form method="POST" action="{{ route('products.store') }}">
  @csrf
  <label>SKU:</label><br>
  <input type="text" name="sku" required><br>

  <label>Nombre:</label><br>
  <input type="text" name="name" required><br>

  <label>Categoría:</label><br>
  <select name="category_id">
    <option value="">-- Sin categoría --</option>
    @foreach($cats as $c)
      <option value="{{ $c->id }}">{{ $c->name }}</option>
    @endforeach
  </select><br>

  <label>Tiene número de serie:</label>
  <input type="checkbox" name="has_serial" value="1"><br>

  <label>Costo promedio:</label><br>
  <input type="number" step="0.01" name="avg_cost" value="0"><br><br>

  <button type="submit">Guardar producto</button>
</form>

@endsection
