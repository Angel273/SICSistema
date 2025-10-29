<!DOCTYPE html><html><head><meta charset="utf-8">
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:12px}
h2{text-align:center;margin:0 0 8px}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #444;padding:4px 6px}
th{background:#eee}
.right{text-align:right} .left{text-align:left}
</style></head><body>
<h2>{{ $title }}</h2>
<table>
<thead><tr>
  <th>SKU</th><th>Producto</th><th>Bodega</th>
  <th class="right">Qty</th><th class="right">Costo Prom.</th><th class="right">Valor</th>
</tr></thead>
<tbody>
@forelse($rows as $r)
<tr>
  <td class="left">{{ $r->sku }}</td>
  <td class="left">{{ $r->product }}</td>
  <td class="left">{{ $r->wh }}</td>
  <td class="right">{{ number_format($r->qty,3) }}</td>
  <td class="right">{{ number_format($r->avg_cost,2) }}</td>
  <td class="right">{{ number_format($r->value,2) }}</td>
</tr>
@empty
<tr><td colspan="6">Sin existencias.</td></tr>
@endforelse
<tr>
  <td colspan="3" class="right"><b>Totales</b></td>
  <td class="right"><b>{{ number_format($tot['qty'],3) }}</b></td>
  <td></td>
  <td class="right"><b>{{ number_format($tot['value'],2) }}</b></td>
</tr>
</tbody></table>
<p style="text-align:center;margin-top:10px">Generado el {{ date('d/m/Y H:i') }}</p>
</body></html>
