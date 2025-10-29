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
<p><b>Saldo inicial:</b> {{ number_format($opening,2) }}</p>
<table>
<thead><tr>
  <th>Fecha</th><th>Asiento</th><th>Descripción</th><th class="right">Debe</th><th class="right">Haber</th><th class="right">Saldo</th>
</tr></thead>
<tbody>
@forelse($rows as $r)
<tr>
  <td class="left">{{ $r->date }}</td>
  <td class="left">#{{ $r->asiento }}</td>
  <td class="left">{{ $r->description }}</td>
  <td class="right">{{ $r->debit ? number_format($r->debit,2) : '' }}</td>
  <td class="right">{{ $r->credit ? number_format($r->credit,2) : '' }}</td>
  <td class="right">{{ number_format($r->balance,2) }}</td>
</tr>
@empty
<tr><td colspan="6">Sin movimientos en el período.</td></tr>
@endforelse
<tr>
  <td colspan="3" class="right"><b>Totales</b></td>
  <td class="right"><b>{{ number_format($tot['debe'],2) }}</b></td>
  <td class="right"><b>{{ number_format($tot['haber'],2) }}</b></td>
  <td class="right"><b>{{ number_format($tot['closing'],2) }}</b></td>
</tr>
</tbody></table>
<p style="text-align:center;margin-top:10px">Generado el {{ date('d/m/Y H:i') }}</p>
</body></html>
