<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function diario(Request $r)
    {
        $desde = $r->input('desde', date('Y-m-01'));
        $hasta = $r->input('hasta', date('Y-m-t'));

        // Líneas del diario en el período
        $rows = DB::table('journal_entries as je')
            ->join('journal_lines as jl','jl.journal_entry_id','=','je.id')
            ->join('accounts as a','a.id','=','jl.account_id')
            ->select(
                'je.id as asiento','je.date','je.description',
                'a.code','a.name as account',
                'jl.debit','jl.credit'
            )
            ->whereBetween('je.date',[$desde,$hasta])
            ->orderBy('je.date')->orderBy('je.id')->orderBy('a.code')
            ->get();

        // Totales por asiento para validar balance
        $balances = DB::table('journal_entries as je')
            ->join('journal_lines as jl','jl.journal_entry_id','=','je.id')
            ->select('je.id')
            ->selectRaw('ROUND(SUM(jl.debit),2) as debe, ROUND(SUM(jl.credit),2) as haber')
            ->whereBetween('je.date',[$desde,$hasta])
            ->groupBy('je.id')
            ->pluck('debe','je.id')
            ->map(function($debe,$id) use (&$balances,$desde,$hasta){
                return null; // placeholder, sólo para estructura
            });

        // Rehacer balances bien (pluck doble)
            $sum = DB::table('journal_entries as je')
        ->join('journal_lines as jl','jl.journal_entry_id','=','je.id')
        ->whereBetween('je.date', [$desde, $hasta])
        ->groupBy('je.id')
        ->select('je.id')
        ->selectRaw('ROUND(SUM(jl.debit),2)  as debe')
        ->selectRaw('ROUND(SUM(jl.credit),2) as haber')
        ->get();

    // Mapa: asiento_id => ['debe'=>x, 'haber'=>y]
    $balances = [];
    foreach ($sum as $row) {
        $balances[$row->id] = ['debe' => (float)$row->debe, 'haber' => (float)$row->haber];
    }

        return view('reports/diario', compact('rows','balances','desde','hasta'));
    }

    public function mayor(Request $r)
{
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));
    $code  = trim((string)$r->input('account', ''));

    // Para el selector
    $accounts = DB::table('accounts')->orderBy('code')->get();

    // Si no se eligió cuenta, mostramos sólo el filtro
    if ($code === '') {
        return view('reports.mayor', [
            'accounts' => $accounts,
            'desde'    => $desde,
            'hasta'    => $hasta,
            'acc'      => null,
            'opening'  => 0.0,
            'rows'     => collect(),
            'totals'   => ['debe'=>0.0,'haber'=>0.0,'closing'=>0.0],
        ]);
    }

    // Buscar cuenta por código
    $acc = DB::table('accounts')->where('code',$code)->first();
    abort_if(!$acc, 404, 'Cuenta no encontrada');

    // Saldo inicial (hasta el día anterior a "desde")
    $opening = (float) DB::table('journal_lines as jl')
        ->join('journal_entries as je','je.id','=','jl.journal_entry_id')
        ->where('jl.account_id', $acc->id)
        ->where('je.date', '<', $desde)
        ->selectRaw('COALESCE(SUM(jl.debit - jl.credit),0) as saldo')
        ->value('saldo');

    // Movimientos del periodo
    $lines = DB::table('journal_entries as je')
        ->join('journal_lines as jl','jl.journal_entry_id','=','je.id')
        ->where('jl.account_id', $acc->id)
        ->whereBetween('je.date', [$desde, $hasta])
        ->orderBy('je.date')->orderBy('je.id')
        ->select('je.date','je.id as asiento','je.description','jl.debit','jl.credit')
        ->get();

    // Cálculo de saldo corrido y totales
    $balance = $opening;
    $rows = [];
    $totD = 0.0; $totC = 0.0;

    foreach ($lines as $L) {
        $totD += (float)$L->debit;
        $totC += (float)$L->credit;
        $balance += (float)$L->debit - (float)$L->credit;

        $rows[] = (object)[
            'date'        => $L->date,
            'asiento'     => $L->asiento,
            'description' => $L->description,
            'debit'       => (float)$L->debit,
            'credit'      => (float)$L->credit,
            'balance'     => (float)$balance,
        ];
    }

    $totals = [
        'debe'   => round($totD,2),
        'haber'  => round($totC,2),
        'closing'=> round($balance,2),
    ];

    return view('reports.mayor', compact('accounts','desde','hasta','acc','opening','rows','totals','code'));
}
public function libroVentas(Request $r)
{
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));
    $store = $r->input('store_id'); // opcional

    // selector de tiendas
    $stores = DB::table('stores')->orderBy('name')->get();

    $q = DB::table('sales as s')
        ->join('customers as c','c.id','=','s.customer_id')
        ->select('s.id','s.date','s.total','s.subtotal','s.tax','c.name as customer','s.store_id');

    if ($store) $q->where('s.store_id',$store);
    $rows = $q->whereBetween('s.date',[$desde,$hasta])
              ->orderBy('s.date')->orderBy('s.id')
              ->get();

    // Totales libro
    $tot = (object)[
        'gravado' => round($rows->sum('subtotal'),2),
        'iva'     => round($rows->sum('tax'),2),
        'total'   => round($rows->sum('total'),2),
        'docs'    => $rows->count(),
    ];

    return view('reports.ventas', compact('rows','desde','hasta','stores','store','tot'));
}


public function libroCompras(Request $r)
{
    // Filtros
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));

    // Catálogos para combos (si los usás en la vista)
    $suppliers  = DB::table('suppliers')->orderBy('name')->get();
    $stores     = DB::table('stores')->orderBy('name')->get();
    $warehouses = DB::table('warehouses')->orderBy('name')->get();

    // Query base (usa SOLO columnas reales)
    $q = DB::table('purchases as p')
        ->leftJoin('suppliers as s', 's.id', '=', 'p.supplier_id')
        ->leftJoin('warehouses as w', 'w.id', '=', 'p.warehouse_id')
        ->leftJoin('stores as st', 'st.id', '=', 'w.store_id')
        ->selectRaw("
            p.id, p.date, p.payment_term, p.due_date,
            p.subtotal, p.tax, p.total,
            p.warehouse_id,
            s.name  as supplier_name,
            w.code  as warehouse_code, w.name as warehouse_name,
            st.code as store_code,    st.name as store_name
        ");

    // Aplicar filtros
    if ($r->filled('desde'))        $q->whereDate('p.date', '>=', $r->desde);
    if ($r->filled('hasta'))        $q->whereDate('p.date', '<=', $r->hasta);
    if ($r->filled('supplier_id'))  $q->where('p.supplier_id', $r->supplier_id);
    if ($r->filled('store_id'))     $q->where('w.store_id',   $r->store_id);
    if ($r->filled('warehouse_id')) $q->where('p.warehouse_id', $r->warehouse_id);
    if ($r->filled('payment_term')) $q->where('p.payment_term', $r->payment_term);

    $rows = $q->orderByDesc('p.date')->orderByDesc('p.id')->paginate(25);

    // Totales (en página actual, si querés totales de todo usa ->get())
    $sum_gravado = $rows->sum('subtotal');
    $sum_iva     = $rows->sum('tax');
    $sum_total   = $rows->sum('total');

    // Links de export que heredan los filtros actuales
    $exportCsv = route('exports.compras.csv', $r->query());
    $exportPdf = route('exports.compras.pdf', $r->query());

    return view('reports.compras', compact(
        'rows','suppliers','stores','warehouses',
        'desde','hasta',
        'sum_gravado','sum_iva','sum_total',
        'exportCsv','exportPdf'
    ));
}



public function inventario(Request $r)
{
    $wh   = $r->input('warehouse_id');     // opcional
    $q    = trim((string)$r->input('q'));  // buscar por sku/nombre

    // selects
    $warehouses = DB::table('warehouses')->orderBy('name')->get();

    $rows = DB::table('product_stocks as ps')
        ->join('products as p','p.id','=','ps.product_id')
        ->join('warehouses as w','w.id','=','ps.warehouse_id')
        ->when($wh, fn($t)=>$t->where('ps.warehouse_id',$wh))
        ->when($q, function($t) use ($q){
            $like = "%{$q}%";
            $t->where(function($t) use ($like){
                $t->where('p.sku','like',$like)->orWhere('p.name','like',$like);
            });
        })
        ->select('p.id as product_id','p.sku','p.name','p.avg_cost','w.code as wh_code','w.name as wh_name')
        ->selectRaw('ROUND(ps.qty,3) as qty')
        ->selectRaw('ROUND(ps.qty * p.avg_cost,2) as value')
        ->orderBy('p.sku')->orderBy('w.code')
        ->get();

    // Totales (globales y por bodega)
    $total = (object)[
        'qty'   => (float) $rows->sum('qty'),
        'value' => round((float) $rows->sum('value'), 2),
    ];
    $byWh = $rows->groupBy('wh_code')->map(function($g){
        return [
            'wh_name' => $g->first()->wh_name,
            'qty'     => (float) $g->sum('qty'),
            'value'   => round((float) $g->sum('value'),2),
        ];
    });

    return view('reports.inventario', compact('rows','total','byWh','warehouses','wh','q'));
}

public function kardex(Request $r)
{
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));
    $pid   = $r->input('product_id');      // requerido en la práctica
    $wh    = $r->input('warehouse_id');    // opcional

    // selects
    $products   = DB::table('products')->orderBy('sku')->get();
    $warehouses = DB::table('warehouses')->orderBy('code')->get();

    if(!$pid){
        return view('reports.kardex', [
            'products'=>$products,'warehouses'=>$warehouses,'desde'=>$desde,'hasta'=>$hasta,
            'rows'=>collect(),'opening'=>['qty'=>0,'value'=>0],'totals'=>['in'=>0,'out'=>0,'value_in'=>0,'value_out'=>0],
            'prod'=>null,'wh'=>$wh,'pid'=>$pid
        ]);
    }

    $prod = DB::table('products')->where('id',$pid)->first();

    // Saldo inicial (antes de "desde")
    $openQ = DB::table('kardex')
        ->when($wh, fn($t)=>$t->where('warehouse_id',$wh))
        ->where('product_id',$pid)
        ->where('occurred_at','<', $desde.' 00:00:00')
        ->selectRaw("COALESCE(SUM(CASE WHEN movement_type='IN'  THEN qty ELSE -qty END),0) as q")->value('q');

    $openV = DB::table('kardex')
        ->when($wh, fn($t)=>$t->where('warehouse_id',$wh))
        ->where('product_id',$pid)
        ->where('occurred_at','<', $desde.' 00:00:00')
        ->selectRaw("COALESCE(SUM(CASE WHEN movement_type='IN'  THEN qty*unit_cost ELSE -qty*unit_cost END),0) as v")->value('v');

    $opening = ['qty'=>(float)$openQ, 'value'=>round((float)$openV,2)];

    // Movimientos del período
    $lines = DB::table('kardex as k')
        ->join('warehouses as w','w.id','=','k.warehouse_id')
        ->where('k.product_id',$pid)
        ->when($wh, fn($t)=>$t->where('k.warehouse_id',$wh))
        ->whereBetween('k.occurred_at', [$desde.' 00:00:00', $hasta.' 23:59:59'])
        ->orderBy('k.occurred_at')->orderBy('k.id')
        ->select('k.occurred_at','k.movement_type','k.qty','k.unit_cost','k.ref_type','k.ref_id','w.code as wh_code')
        ->get();

    $rows = [];
    $balQ = $opening['qty'];
    $balV = $opening['value'];

    $totInQ = 0; $totOutQ = 0; $totInV = 0; $totOutV = 0;

    foreach ($lines as $L) {
        $qty = (float)$L->qty;
        $val = round($qty * (float)$L->unit_cost, 2);

        if ($L->movement_type === 'IN') {
            $balQ += $qty;    $balV += $val;
            $totInQ += $qty;  $totInV += $val;
        } else { // OUT
            $balQ -= $qty;    $balV -= $val;
            $totOutQ += $qty; $totOutV += $val;
        }

        $rows[] = (object)[
            'date'      => $L->occurred_at,
            'wh'        => $L->wh_code,
            'type'      => $L->movement_type,
            'qty'       => $qty,
            'unit_cost' => (float)$L->unit_cost,
            'value'     => $val,
            'ref'       => "{$L->ref_type}:{$L->ref_id}",
            'bal_qty'   => (float)$balQ,
            'bal_val'   => (float)round($balV,2),
        ];
    }

    $totals = ['in'=>$totInQ,'out'=>$totOutQ,'value_in'=>round($totInV,2),'value_out'=>round($totOutV,2)];

    return view('reports.kardex', compact('products','warehouses','desde','hasta','rows','opening','totals','prod','wh','pid'));
}
public function cxc(Request $r)
{
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));
    $customer = $r->input('customer_id');

    // Lista para el filtro
    $customers = DB::table('customers')->orderBy('name')->get();

    // Ventas a crédito dentro del período
    $q = DB::table('sales as s')
        ->join('customers as c','c.id','=','s.customer_id')
        ->select('s.id','s.date','s.total','s.payment_term','s.customer_id','c.name as customer');

    $q->where('s.payment_term','=','CREDITO')
      ->whereBetween('s.date', [$desde,$hasta]);

    if ($customer) $q->where('s.customer_id', $customer);

    $ventas = $q->get();

    // Traer abonos por venta
    $abonos = DB::table('receipts')
        ->select('sale_id', DB::raw('SUM(amount) as pagado'))
        ->groupBy('sale_id')
        ->pluck('pagado','sale_id');

    // Calcular saldos
    $rows = [];
    foreach($ventas as $v){
        $pagado = (float)($abonos[$v->id] ?? 0);
        $saldo  = round($v->total - $pagado, 2);
        if ($saldo > 0.01) {
            $rows[] = (object)[
                'id'       => $v->id,
                'date'     => $v->date,
                'customer' => $v->customer,
                'total'    => $v->total,
                'pagado'   => $pagado,
                'saldo'    => $saldo,
            ];
        }
    }

    $tot = [
        'total'  => round(collect($rows)->sum('total'),2),
        'pagado' => round(collect($rows)->sum('pagado'),2),
        'saldo'  => round(collect($rows)->sum('saldo'),2),
    ];

    return view('reports.cxc', compact('rows','desde','hasta','customers','customer','tot'));
}


public function cxp(Request $r)
{
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));
    $supplier = $r->input('supplier_id');

    $suppliers = DB::table('suppliers')->orderBy('name')->get();

    // Compras a crédito dentro del período
    $q = DB::table('purchases as p')
        ->join('suppliers as s','s.id','=','p.supplier_id')
        ->select('p.id','p.date','p.total','p.payment_term','p.supplier_id','s.name as supplier');

    $q->where('p.payment_term','=','CREDITO')
      ->whereBetween('p.date', [$desde,$hasta]);

    if ($supplier) $q->where('p.supplier_id',$supplier);

    $compras = $q->get();

    // Pagos por compra
    $pagos = DB::table('payments')
        ->select('purchase_id', DB::raw('SUM(amount) as pagado'))
        ->groupBy('purchase_id')
        ->pluck('pagado','purchase_id');

    $rows = [];
    foreach($compras as $p){
        $pagado = (float)($pagos[$p->id] ?? 0);
        $saldo  = round($p->total - $pagado, 2);
        if ($saldo > 0.01) {
            $rows[] = (object)[
                'id'        => $p->id,
                'date'      => $p->date,
                'supplier'  => $p->supplier,
                'total'     => $p->total,
                'pagado'    => $pagado,
                'saldo'     => $saldo,
            ];
        }
    }

    $tot = [
        'total'  => round(collect($rows)->sum('total'),2),
        'pagado' => round(collect($rows)->sum('pagado'),2),
        'saldo'  => round(collect($rows)->sum('saldo'),2),
    ];

    return view('reports.cxp', compact('rows','desde','hasta','suppliers','supplier','tot'));
}


}

