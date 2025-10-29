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
<p><b>Saldo inicial:</b> Qty {{ number_format($opening['qty'],3) }} | Valor {{ number_format($opening['value'],2) }}</p>
<table>
<thead><tr>
  <th>Fecha</th><th>Bodega</th><th>Tipo</th>
  <th class="right">Qty</th><th class="right">Costo</th><th class="right">Valor</th>
  <th>Ref</th><th class="right">Saldo Qty</th><th class="right">Saldo Valor</th>
</tr></thead>
<tbody>
@forelse($rows as $r)
<tr>
  <td class="left">{{ $r->date }}</td>
  <td class="left">{{ $r->wh }}</td>
  <td class="left">{{ $r->type }}</td>
  <td class="right">{{ number_format($r->qty,3) }}</td>
  <td class="right">{{ number_format($r->unit_cost,2) }}</td>
  <td class="right">{{ number_format($r->value,2) }}</td>
  <td class="left">{{ $r->ref }}</td>
  <td class="right">{{ number_format($r->bal_qty,3) }}</td>
  <td class="right">{{ number_format($r->bal_val,2) }}</td>
</tr>
@empty
<tr><td colspan="9">Sin movimientos.</td></tr>
@endforelse
</tbody></table>
<p style="text-align:center;margin-top:10px">Generado el {{ date('d/m/Y H:i') }}</p>
</body></html>
