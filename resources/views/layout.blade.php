<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>SIC Retail</title>
  <style>
    body { font-family: Arial; margin: 24px; background: #0b0b0b; color: #eee; }
    a, button { color: #4be4b6; text-decoration: none; }
    a:hover { text-decoration: underline; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border-bottom: 1px solid #333; padding: 8px; text-align: left; }
    th { background: #1a1a1a; }
    .ok { color: #6aff80; }
    .error { color: #ff7070; }
  </style>
</head>
<body>
  <header>
    <h1>SIC Retail</h1>
<nav style="margin-bottom:8px;">
  <a href="{{ route('dashboard') }}">🏠 Dashboard</a> |
  <a href="/stores">🏬 Tiendas</a> |
  <a href="/warehouses">📦 Bodegas</a> |
  <a href="/suppliers">🏭 Proveedores</a> |
  <a href="/products">🧰 Productos</a> |
  <a href="{{ route('purchases.create') }}">🧾 Nueva compra</a> |
  <a href="{{ route('sales.create') }}">🛒 Nueva venta</a> |
  <a href="{{ route('receipts.create') }}">💵 Nuevo cobro</a> |
  <a href="{{ route('payments.create') }}">🏦 Nuevo pago</a> |
  <a href="{{ route('reports.diario') }}">📘 Diario</a> |
  <a href="{{ route('reports.mayor') }}">📙 Mayor</a> |
 <a href="{{ route('reports.ventas') }}">📗 Ventas</a> |
 <a href="{{ route('reports.compras') }}">📕 Compras</a>
| <a href="{{ route('reports.inventario') }}">📦 Inventario</a>
| <a href="{{ route('reports.kardex') }}">📜 Kardex</a>
| <a href="{{ route('reports.cxc') }}">💵 CxC</a>
| <a href="{{ route('reports.cxp') }}">🏦 CxP</a>


</nav>

{{-- Mensajes flash (éxito/errores) --}}
@if(session('ok'))
  <div style="background:#c6ffc6; color:#000; padding:8px; border-radius:6px; margin-bottom:8px;">
    ✅ {{ session('ok') }}
  </div>
@endif

@if(session('error'))
  <div style="background:#ffc6c6; color:#000; padding:8px; border-radius:6px; margin-bottom:8px;">
    ❌ {{ session('error') }}
  </div>
@endif

@if ($errors->any())
  <div style="background:#f66; color:#000; padding:8px; border-radius:6px; margin-bottom:8px;">
    <b>Errores:</b>
    <ul style="margin:6px 0 0 16px;">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif


  
  </header>

  <main>
    {{-- Aquí Laravel insertará el contenido de cada página --}}
    @yield('content')
  </main>
</body>
</html> 

