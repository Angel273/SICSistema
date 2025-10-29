<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h2 { text-align:center; margin-bottom: 10px; }
    table { width:100%; border-collapse: collapse; margin-top:10px; }
    th, td { border:1px solid #444; padding:4px 6px; text-align:right; }
    th { background:#eee; text-align:center; }
    td:first-child, td:nth-child(2), td:nth-child(3) { text-align:left; }
  </style>
</head>
<body>
  <h2>{{ $title }}</h2>
  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Factura</th>
        <th>Cliente</th>
        <th>Gravado</th>
        <th>IVA</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
      <tr>
        <td>{{ $r->date }}</td>
        <td>VTA-{{ $r->id }}</td>
        <td>{{ $r->customer }}</td>
        <td>{{ number_format($r->subtotal,2) }}</td>
        <td>{{ number_format($r->tax,2) }}</td>
        <td>{{ number_format($r->total,2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <p style="margin-top:20px;text-align:center;">Generado el {{ date('d/m/Y H:i') }}</p>
</body>
</html>

