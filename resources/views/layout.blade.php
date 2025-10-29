<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','SIC SISTEMA')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>

  {{-- Topbar --}}
  <div class="topbar d-flex align-items-center px-3 justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <span class="brand">SIC Retail</span>
      <div class="separator" style="width:1px;height:24px"></div>
      <ol class="breadcrumb mb-0">
        @yield('breadcrumb')
      </ol>
    </div>
    <div class="d-flex align-items-center gap-2">
      @yield('actions')
    </div>
  </div>

  <div class="app-shell">
    {{-- Sidebar --}}
    <aside class="app-sidebar p-3">
      <div class="mb-3 text-uppercase small text-muted">Navegación</div>
      <ul class="list-unstyled">
        <li><a class="d-flex align-items-center gap-2 py-2 text-decoration-none" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="mt-2 text-uppercase small text-muted">Operaciones</li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('products.index') }}"><i class="bi bi-box-seam"></i> Productos</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('purchases.create') }}"><i class="bi bi-bag-plus"></i> Registrar compra</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('sales.create') }}"><i class="bi bi-receipt"></i> Registrar venta</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('receipts.create') }}"><i class="bi bi-cash-coin"></i> Cobros (CxC)</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('payments.create') }}"><i class="bi bi-wallet2"></i> Pagos (CxP)</a></li>

        <li class="mt-3 text-uppercase small text-muted">Maestros</li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('stores.index') }}"><i class="bi bi-shop"></i> Tiendas</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('warehouses.index') }}"><i class="bi bi-building"></i> Bodegas</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('suppliers.index') }}"><i class="bi bi-truck"></i> Proveedores</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('customers.master.index') }}"><i class="bi bi-people"></i> Clientes</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('accounts.index') }}"><i class="bi bi-book"></i> Catalogo de cuentas</a></li>

        <li class="mt-3 text-uppercase small text-muted">Reportes</li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('reports.diario') }}"><i class="bi bi-journal-text"></i> Libro Diario</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('reports.mayor') }}"><i class="bi bi-kanban"></i> Libro Mayor</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('reports.ventas') }}"><i class="bi bi-bar-chart"></i> Libro de Ventas</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('reports.compras') }}"><i class="bi bi-clipboard-data"></i> Libro de Compras</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('reports.inventario') }}"><i class="bi bi-boxes"></i> Inventario</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('reports.cxp') }}"><i class="bi bi-receipt"></i>Cuentas Por Pagar</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('reports.cxc') }}"><i class="bi bi-currency-dollar"></i>Cuentas Por Cobrar</a></li>
        <li><a class="d-flex gap-2 py-2 text-decoration-none" href="{{ route('reports.kardex') }}"><i class="bi bi-archive"></i> Kardex</a></li>
      </ul>
    </aside>

    {{-- Contenido --}}
    <main class="app-content">
      @include('partials.flash') {{-- mensajes success/error --}}
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="page-title">@yield('page_title','')</h1>
        <div>@yield('page_actions')</div>
      </div>
      @yield('content')
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

