@extends('layout')

@section('title','Productos')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item active" aria-current="page">Productos</li>
@endsection

@section('page_title','Productos')
@section('page_actions')
  <a href="{{ route('products.create') }}" class="btn btn-brand">
    <i class="bi bi-plus-lg me-1"></i> Nuevo producto
  </a>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <form class="row g-2 mb-3" method="GET">
        <div class="col-md-4">
          <input type="search" name="q" class="form-control" placeholder="Buscar por nombre o SKU…" value="{{ request('q') }}">
        </div>

      </form>

      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Producto</th>
              <th>Precio</th>
              <th>Existencias</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $r)
              <tr>
                <td class="text-muted">{{ $r->sku }}</td>
                <td class="fw-semibold">{{ $r->name }}</td>
                <td>${{ number_format($r->avg_cost,2) }}</td>
                <td><span class="badge badge-soft">{{ $r->stock_sum ?? 0 }}</span></td>
                <td class="text-end">
                  <a href="{{ route('products.edit', ['product' => $r->id]) }}" class="btn btn-sm btn-outline-secondary">
  <i class="bi bi-pencil"></i>
</a>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">Sin resultados</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{ $rows->withQueryString()->links() }}
    </div>
  </div>
@endsection

