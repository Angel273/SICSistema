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
  <th>Fecha</th><th>Doc</th><th>Cliente</th>
  <th class="right">Total</th><th class="right">Pagado</th><th class="right">Saldo</th>
</tr></thead>
<tbody>
@forelse($rows as $r)
<tr>
  <td class="left">{{ $r->date }}</td>
  <td class="left">{{ $r->doc }}</td>
  <td class="left">{{ $r->customer }}</td>
  <td class="right">{{ number_format($r->total,2) }}</td>
  <td class="right">{{ number_format($r->pagado,2) }}</td>
  <td class="right">{{ number_format($r->saldo,2) }}</td>
</tr>
@empty
<tr><td colspan="6">Sin cuentas pendientes.</td></tr>
@endforelse
<tr>
  <td colspan="3" class="right"><b>Totales</b></td>
  <td class="right"><b>{{ number_format($tot['total'],2) }}</b></td>
  <td class="right"><b>{{ number_format($tot['pagado'],2) }}</b></td>
  <td class="right"><b>{{ number_format($tot['saldo'],2) }}</b></td>
</tr>
</tbody></table>
<p style="text-align:center;margin-top:10px">Generado el {{ date('d/m/Y H:i') }}</p>
</body></html>
