@extends('layout')

@section('title','Nuevo Producto')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Productos</a></li>
  <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('page_title','Registrar nuevo producto')

@section('page_actions')
  <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Volver
  </a>
@endsection

@section('content')
<div class="card">
  <div class="card-body">

    {{-- Mensajes de validación --}}
    @if($errors->any())
      <div class="alert alert-danger">
        <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle"></i> Corrige los siguientes errores:</div>
        <ul class="mb-0">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('products.store') }}" method="POST" class="row g-3">
      @csrf

      {{-- SKU --}}
      <div class="col-md-3">
        <label class="form-label">Código (SKU)</label>
        <input type="text" name="sku" value="{{ old('sku') }}" class="form-control" required>
      </div>

      {{-- Nombre --}}
      <div class="col-md-6">
        <label class="form-label">Nombre del producto</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
      </div>

      {{-- Categoría --}}
      <div class="col-md-3">
        <label class="form-label">Categoría</label>
        <select name="category_id" class="form-select" required>
          <option value="">Seleccione...</option>
          @foreach($cats as $c)
            <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>
              {{ $c->name }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Costo promedio --}}
      <div class="col-md-3">
        <label class="form-label">Costo promedio ($)</label>
        <input type="number" step="0.01" name="avg_cost" value="{{ old('avg_cost') }}" class="form-control">
      </div>

      {{-- ¿Tiene número de serie? --}}
      <div class="col-md-3">
        <label class="form-label d-block">¿Requiere número de serie?</label>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="has_serial" name="has_serial" {{ old('has_serial') ? 'checked' : '' }}>
          <label class="form-check-label" for="has_serial">Sí</label>
        </div>
      </div>

      <div class="col-12 mt-4 text-end">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
          <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <button class="btn btn-brand">
          <i class="bi bi-check2-circle me-1"></i> Guardar producto
        </button>
      </div>
    </form>

  </div>
</div>
@endsection
