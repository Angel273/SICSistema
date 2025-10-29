@extends('layout')

@section('title','Editar Producto')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Productos</a></li>
  <li class="breadcrumb-item active">Editar</li>
@endsection

@section('page_title','Editar producto')

@section('page_actions')
  <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Volver
  </a>

  {{-- Botón eliminar (abre modal) --}}
  <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmDelete">
    <i class="bi bi-trash3"></i> Eliminar
  </button>
@endsection

@section('content')
<div class="card">
  <div class="card-body">

    {{-- Errores de validación --}}
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

    <form action="{{ route('products.update', $p->id) }}" method="POST" class="row g-3">
      @csrf
      @method('PUT')

      {{-- SKU --}}
      <div class="col-md-3">
        <label class="form-label">Código (SKU)</label>
        <input type="text" name="sku"
               value="{{ old('sku', $p->sku) }}"
               class="form-control" required>
      </div>

      {{-- Nombre --}}
      <div class="col-md-6">
        <label class="form-label">Nombre del producto</label>
        <input type="text" name="name"
               value="{{ old('name', $p->name) }}"
               class="form-control" required>
      </div>

      {{-- Categoría --}}
      <div class="col-md-3">
        <label class="form-label">Categoría</label>
        <select name="category_id" class="form-select" required>
          @foreach($cats as $c)
            <option value="{{ $c->id }}"
              {{ (string)old('category_id', $p->category_id) === (string)$c->id ? 'selected' : '' }}>
              {{ $c->name }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Costo promedio --}}
      <div class="col-md-3">
        <label class="form-label">Costo promedio ($)</label>
        <input type="number" step="0.01" name="avg_cost"
               value="{{ old('avg_cost', $p->avg_cost) }}"
               class="form-control">
      </div>

      {{-- ¿Requiere número de serie? --}}
      <div class="col-md-3">
        <label class="form-label d-block">¿Requiere número de serie?</label>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="has_serial" name="has_serial"
                 {{ old('has_serial', $p->has_serial) ? 'checked' : '' }}>
          <label class="form-check-label" for="has_serial">Sí</label>
        </div>
      </div>

      <div class="col-12 mt-4 text-end">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
          <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <button class="btn btn-brand">
          <i class="bi bi-check2-circle me-1"></i> Guardar cambios
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal de confirmación de borrado --}}
<div class="modal fade" id="confirmDelete" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Seguro que deseas eliminar <strong>{{ $p->name }}</strong>? Esta acción no se puede deshacer.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <form action="{{ route('products.destroy', $p->id) }}" method="POST">
          @csrf @method('DELETE')
          <button class="btn btn-outline-danger"><i class="bi bi-trash3"></i> Eliminar</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
