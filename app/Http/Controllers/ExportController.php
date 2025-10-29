<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF; // barryvdh/laravel-dompdf
use App\Exports\VentasExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    // ---- Reutilizamos una query para NO duplicar lógica ----
    protected function ventasQuery($desde, $hasta, $storeId = null)
    {
        $q = DB::table('sales as s')
            ->join('customers as c','c.id','=','s.customer_id')
            ->select('s.id','s.date','c.name as customer','s.subtotal','s.tax','s.total','s.store_id')
            ->whereBetween('s.date', [$desde, $hasta])
            ->orderBy('s.date')->orderBy('s.id');

        if ($storeId) $q->where('s.store_id',$storeId);
        return $q->get();
    }

    private function comprasBaseQuery(Request $r)
{
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

    if ($r->filled('desde'))        $q->whereDate('p.date', '>=', $r->desde);
    if ($r->filled('hasta'))        $q->whereDate('p.date', '<=', $r->hasta);
    if ($r->filled('supplier_id'))  $q->where('p.supplier_id', $r->supplier_id);
    if ($r->filled('store_id'))     $q->where('w.store_id',   $r->store_id);
    if ($r->filled('warehouse_id')) $q->where('p.warehouse_id', $r->warehouse_id);
    if ($r->filled('payment_term')) $q->where('p.payment_term', $r->payment_term);

    return $q->orderByDesc('p.date')->orderByDesc('p.id');
}

public function comprasCsv(Request $r)
    {
        $desde = $r->input('desde', date('Y-m-01'));
        $hasta = $r->input('hasta', date('Y-m-t'));
        $wh    = $r->input('warehouse_id');

        $rows = DB::table('purchases as p')
            ->join('suppliers as s','s.id','=','p.supplier_id')
            ->select('p.date','p.id','s.name as supplier','p.subtotal','p.tax','p.total','p.warehouse_id')
            ->whereBetween('p.date', [$desde,$hasta])
            ->when($wh, fn($q)=>$q->where('p.warehouse_id',$wh))
            ->orderBy('p.date')->orderBy('p.id')
            ->get();

        return $this->streamCsv("LibroCompras_{$desde}_{$hasta}.csv",
            ['Fecha','Doc','Proveedor','Gravado','IVA','Total'],
            function() use ($rows) {
                foreach ($rows as $r) yield [
                    $r->date, 'CPA-'.$r->id, $r->supplier, (float)$r->subtotal, (float)$r->tax, (float)$r->total
                ];
            }
        );
    }  

public function comprasPdf(Request $r)
{
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));

    $rows = DB::table('purchases as p')
        ->join('suppliers as s','s.id','=','p.supplier_id')
        ->select('p.date','p.id','s.name as supplier','p.subtotal','p.tax','p.total')
        ->whereBetween('p.date',[$desde,$hasta])
        ->orderBy('p.date')->orderBy('p.id')
        ->get();

    return $this->streamPdf('exports.compras', [
        'title' => "Libro de Compras del $desde al $hasta",
        'rows'  => $rows
    ], "LibroCompras_{$desde}_{$hasta}.pdf");
}


    private function streamPdf($view, $data, $filename)
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');
        return $pdf->stream($filename);
    }

    /* ----------------- Ejemplo: VENTAS ----------------- */
    public function ventasPdf(Request $r)
    {
        $desde = $r->input('desde', date('Y-m-01'));
        $hasta = $r->input('hasta', date('Y-m-t'));

        $rows = DB::table('sales as s')
            ->join('customers as c','c.id','=','s.customer_id')
            ->select('s.date','s.id','c.name as customer','s.subtotal','s.tax','s.total')
            ->whereBetween('s.date',[$desde,$hasta])
            ->orderBy('s.date')->orderBy('s.id')
            ->get();

        return $this->streamPdf('exports.ventas', [
            'title' => "Libro de Ventas del $desde al $hasta",
            'rows'  => $rows
        ], "LibroVentas_{$desde}_{$hasta}.pdf");
    }

    

    public function diarioPdf(Request $r)
{
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));

    $rows = DB::table('journal_entries as je')
        ->join('journal_lines as jl','jl.journal_entry_id','=','je.id')
        ->join('accounts as a','a.id','=','jl.account_id')
        ->select('je.date','je.id as asiento','je.description','a.code','a.name as account','jl.debit','jl.credit')
        ->whereBetween('je.date',[$desde,$hasta])
        ->orderBy('je.date')->orderBy('je.id')->orderBy('a.code')
        ->get();

    $totDebe  = round($rows->sum('debit'),2);
    $totHaber = round($rows->sum('credit'),2);

    return $this->streamPdf('exports.diario', [
        'title' => "Libro Diario del $desde al $hasta",
        'rows'  => $rows,
        'tot'   => ['debe'=>$totDebe,'haber'=>$totHaber]
    ], "LibroDiario_{$desde}_{$hasta}.pdf");
}

/* ----------------- MAYOR (PDF) ----------------- */
public function mayorPdf(Request $r)
{
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));
    $code  = trim((string)$r->input('account',''));

    $acc = $code ? DB::table('accounts')->where('code',$code)->first() : null;
    abort_if(!$acc, 400, 'Falta parámetro ?account=CODE');

    $opening = (float) DB::table('journal_lines as jl')
        ->join('journal_entries as je','je.id','=','jl.journal_entry_id')
        ->where('jl.account_id',$acc->id)->where('je.date','<',$desde)
        ->selectRaw('COALESCE(SUM(jl.debit - jl.credit),0) as saldo')->value('saldo');

    $lines = DB::table('journal_entries as je')
        ->join('journal_lines as jl','jl.journal_entry_id','=','je.id')
        ->where('jl.account_id',$acc->id)
        ->whereBetween('je.date',[$desde,$hasta])
        ->orderBy('je.date')->orderBy('je.id')
        ->select('je.date','je.id as asiento','je.description','jl.debit','jl.credit')
        ->get();

    $balance = $opening; $totD=0; $totC=0;
    $rows = [];
    foreach ($lines as $L) {
        $totD += (float)$L->debit; $totC += (float)$L->credit;
        $balance += (float)$L->debit - (float)$L->credit;
        $rows[] = (object)[
            'date'=>$L->date,'asiento'=>$L->asiento,'description'=>$L->description,
            'debit'=>(float)$L->debit,'credit'=>(float)$L->credit,'balance'=>$balance
        ];
    }

    return $this->streamPdf('exports.mayor', [
        'title'   => "Libro Mayor: {$acc->code} - {$acc->name} ($desde a $hasta)",
        'opening' => $opening,
        'rows'    => $rows,
        'tot'     => ['debe'=>round($totD,2),'haber'=>round($totC,2),'closing'=>round($balance,2)],
        'acc'     => $acc
    ], "LibroMayor_{$acc->code}_{$desde}_{$hasta}.pdf");
}

/* ----------------- INVENTARIO (PDF) ----------------- */
public function inventarioPdf(Request $r)
{
    $wh = $r->input('warehouse_id');
    $q  = trim((string)$r->input('q'));

    $rows = DB::table('product_stocks as ps')
        ->join('products as p','p.id','=','ps.product_id')
        ->join('warehouses as w','w.id','=','ps.warehouse_id')
        ->when($wh, fn($t)=>$t->where('ps.warehouse_id',$wh))
        ->when($q, function($t) use ($q){
            $like = "%{$q}%";
            $t->where(fn($t)=>$t->where('p.sku','like',$like)->orWhere('p.name','like',$like));
        })
        ->select('p.sku','p.name as product','w.code as wh','ps.qty','p.avg_cost')
        ->selectRaw('(ps.qty * p.avg_cost) as value')
        ->orderBy('p.sku')->orderBy('w.code')
        ->get();

    $tot = [
        'qty'   => (float)$rows->sum('qty'),
        'value' => round((float)$rows->sum('value'),2),
    ];

    return $this->streamPdf('exports.inventario', [
        'title' => 'Inventario Valorizado',
        'rows'  => $rows,
        'tot'   => $tot
    ], "InventarioValorizado.pdf");
}

/* ----------------- KARDEX (PDF) ----------------- */
public function kardexPdf(Request $r)
{
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));
    $pid   = $r->input('product_id');
    $wh    = $r->input('warehouse_id');

    abort_if(!$pid, 400, 'Falta ?product_id=');

    $prod = DB::table('products')->where('id',$pid)->first();

    $openQ = DB::table('kardex')->when($wh,fn($t)=>$t->where('warehouse_id',$wh))
        ->where('product_id',$pid)->where('occurred_at','<',$desde.' 00:00:00')
        ->selectRaw("COALESCE(SUM(CASE WHEN movement_type='IN' THEN qty ELSE -qty END),0) q")->value('q');

    $openV = DB::table('kardex')->when($wh,fn($t)=>$t->where('warehouse_id',$wh))
        ->where('product_id',$pid)->where('occurred_at','<',$desde.' 00:00:00')
        ->selectRaw("COALESCE(SUM(CASE WHEN movement_type='IN' THEN qty*unit_cost ELSE -qty*unit_cost END),0) v")->value('v');

    $lines = DB::table('kardex as k')
        ->join('warehouses as w','w.id','=','k.warehouse_id')
        ->where('k.product_id',$pid)->when($wh,fn($t)=>$t->where('k.warehouse_id',$wh))
        ->whereBetween('k.occurred_at', [$desde.' 00:00:00',$hasta.' 23:59:59'])
        ->orderBy('k.occurred_at')->orderBy('k.id')
        ->select('k.occurred_at','w.code as wh','k.movement_type','k.qty','k.unit_cost','k.ref_type','k.ref_id')
        ->get();

    $balQ = (float)$openQ; $balV = (float)$openV;
    $rows = [];
    foreach ($lines as $L) {
        $val = (float)$L->qty * (float)$L->unit_cost;
        if ($L->movement_type === 'IN') { $balQ += $L->qty; $balV += $val; }
        else { $balQ -= $L->qty; $balV -= $val; }
        $rows[] = (object)[
            'date'=>$L->occurred_at,'wh'=>$L->wh,'type'=>$L->movement_type,
            'qty'=>(float)$L->qty,'unit_cost'=>(float)$L->unit_cost,'value'=>$val,
            'ref'=>$L->ref_type.':'.$L->ref_id,'bal_qty'=>$balQ,'bal_val'=>$balV
        ];
    }

    return $this->streamPdf('exports.kardex', [
        'title'   => "Kardex {$prod->sku} - {$prod->name} ($desde a $hasta)",
        'opening' => ['qty'=>$openQ,'value'=>$openV],
        'rows'    => $rows
    ], "Kardex_{$prod->sku}_{$desde}_{$hasta}.pdf");
}

/* ----------------- CxC (PDF) ----------------- */
public function cxcPdf(Request $r)
{
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));
    $customer = $r->input('customer_id');

    $ventas = DB::table('sales as s')
        ->join('customers as c','c.id','=','s.customer_id')
        ->select('s.id','s.date','c.name as customer','s.total')
        ->where('s.payment_term','CREDITO')
        ->whereBetween('s.date',[$desde,$hasta])
        ->when($customer, fn($q)=>$q->where('s.customer_id',$customer))
        ->get();

    $abonos = DB::table('receipts')->select('sale_id', DB::raw('SUM(amount) pagado'))
        ->groupBy('sale_id')->pluck('pagado','sale_id');

    $rows = []; $totT=0; $totP=0; $totS=0;
    foreach ($ventas as $v) {
        $pagado = (float)($abonos[$v->id] ?? 0);
        $saldo  = (float)$v->total - $pagado;
        if ($saldo > 0.01) {
            $totT += (float)$v->total; $totP += $pagado; $totS += $saldo;
            $rows[] = (object)[
                'date'=>$v->date,'doc'=>'VTA-'.$v->id,'customer'=>$v->customer,
                'total'=>(float)$v->total,'pagado'=>$pagado,'saldo'=>$saldo
            ];
        }
    }

    return $this->streamPdf('exports.cxc', [
        'title'=>"Cuentas por Cobrar ($desde a $hasta)",
        'rows'=>$rows,
        'tot'=>['total'=>round($totT,2),'pagado'=>round($totP,2),'saldo'=>round($totS,2)]
    ], "CxC_{$desde}_{$hasta}.pdf");
}

/* ----------------- CxP (PDF) ----------------- */
public function cxpPdf(Request $r)
{
    $desde = $r->input('desde', date('Y-m-01'));
    $hasta = $r->input('hasta', date('Y-m-t'));
    $supplier = $r->input('supplier_id');

    $compras = DB::table('purchases as p')
        ->join('suppliers as s','s.id','=','p.supplier_id')
        ->select('p.id','p.date','s.name as supplier','p.total')
        ->where('p.payment_term','CREDITO')
        ->whereBetween('p.date',[$desde,$hasta])
        ->when($supplier, fn($q)=>$q->where('p.supplier_id',$supplier))
        ->get();

    $pagos = DB::table('payments')->select('purchase_id', DB::raw('SUM(amount) pagado'))
        ->groupBy('purchase_id')->pluck('pagado','purchase_id');

    $rows = []; $totT=0; $totP=0; $totS=0;
    foreach ($compras as $p) {
        $pagado = (float)($pagos[$p->id] ?? 0);
        $saldo  = (float)$p->total - $pagado;
        if ($saldo > 0.01) {
            $totT += (float)$p->total; $totP += $pagado; $totS += $saldo;
            $rows[] = (object)[
                'date'=>$p->date,'doc'=>'CPA-'.$p->id,'supplier'=>$p->supplier,
                'total'=>(float)$p->total,'pagado'=>$pagado,'saldo'=>$saldo
            ];
        }
    }

    return $this->streamPdf('exports.cxp', [
        'title'=>"Cuentas por Pagar ($desde a $hasta)",
        'rows'=>$rows,
        'tot'=>['total'=>round($totT,2),'pagado'=>round($totP,2),'saldo'=>round($totS,2)]
    ], "CxP_{$desde}_{$hasta}.pdf");
}
    

    private function streamCsv(string $filename, array $headings, \Closure $rowGenerator)
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control'       => 'no-store, no-cache',
        ];

        return response()->stream(function() use ($headings, $rowGenerator) {
            $out = fopen('php://output', 'w');
            // BOM para que Excel detecte UTF-8
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            // Encabezados
            fputcsv($out, $headings);
            // Filas
            foreach ($rowGenerator() as $row) {
                // asegurar números en decimal con punto
                foreach ($row as &$v) {
                    if (is_float($v) || is_int($v)) $v = number_format((float)$v, 2, '.', '');
                }
                fputcsv($out, $row);
            }
            fclose($out);
        }, 200, $headers);
    }

    /* ------------------- Ventas ------------------- */
    public function ventasCsv(Request $r)
    {
        $desde = $r->input('desde', date('Y-m-01'));
        $hasta = $r->input('hasta', date('Y-m-t'));
        $store = $r->input('store_id');

        $rows = DB::table('sales as s')
            ->join('customers as c','c.id','=','s.customer_id')
            ->select('s.date','s.id','c.name as customer','s.subtotal','s.tax','s.total','s.store_id')
            ->whereBetween('s.date', [$desde,$hasta])
            ->when($store, fn($q)=>$q->where('s.store_id',$store))
            ->orderBy('s.date')->orderBy('s.id')
            ->get();

        return $this->streamCsv("LibroVentas_{$desde}_{$hasta}.csv",
            ['Fecha','Doc','Cliente','Gravado','IVA','Total'],
            function() use ($rows) {
                foreach ($rows as $r) yield [
                    $r->date, 'VTA-'.$r->id, $r->customer, (float)$r->subtotal, (float)$r->tax, (float)$r->total
                ];
            }
        );
    }

    

    /* ------------------- Diario ------------------- */
    public function diarioCsv(Request $r)
    {
        $desde = $r->input('desde', date('Y-m-01'));
        $hasta = $r->input('hasta', date('Y-m-t'));

        $rows = DB::table('journal_entries as je')
            ->join('journal_lines as jl','jl.journal_entry_id','=','je.id')
            ->join('accounts as a','a.id','=','jl.account_id')
            ->select('je.date','je.id as asiento','je.description','a.code','a.name as account','jl.debit','jl.credit')
            ->whereBetween('je.date',[$desde,$hasta])
            ->orderBy('je.date')->orderBy('je.id')->orderBy('a.code')
            ->get();

        return $this->streamCsv("LibroDiario_{$desde}_{$hasta}.csv",
            ['Fecha','Asiento','Descripción','Cuenta','Debe','Haber'],
            function() use ($rows) {
                foreach ($rows as $r) yield [
                    $r->date, $r->asiento, $r->description, "{$r->code} - {$r->account}",
                    (float)$r->debit, (float)$r->credit
                ];
            }
        );
    }

    /* ------------------- Mayor ------------------- */
    public function mayorCsv(Request $r)
    {
        $desde = $r->input('desde', date('Y-m-01'));
        $hasta = $r->input('hasta', date('Y-m-t'));
        $code  = trim((string)$r->input('account',''));

        $acc = $code ? DB::table('accounts')->where('code',$code)->first() : null;
        abort_if(!$acc, 400, 'Seleccione una cuenta (param account=CODE)');

        $opening = (float) DB::table('journal_lines as jl')
            ->join('journal_entries as je','je.id','=','jl.journal_entry_id')
            ->where('jl.account_id',$acc->id)->where('je.date','<',$desde)
            ->selectRaw('COALESCE(SUM(jl.debit - jl.credit),0) as saldo')->value('saldo');

        $lines = DB::table('journal_entries as je')
            ->join('journal_lines as jl','jl.journal_entry_id','=','je.id')
            ->where('jl.account_id',$acc->id)
            ->whereBetween('je.date',[$desde,$hasta])
            ->orderBy('je.date')->orderBy('je.id')
            ->select('je.date','je.id as asiento','je.description','jl.debit','jl.credit')
            ->get();

        $balance = $opening;

        return $this->streamCsv("LibroMayor_{$acc->code}_{$desde}_{$hasta}.csv",
            ['Cuenta','Fecha','Asiento','Descripción','Debe','Haber','Saldo'],
            function() use ($acc,$lines,&$balance) {
                yield [$acc->code.' - '.$acc->name,'','','Saldo inicial','','',(float)$balance];
                foreach ($lines as $L) {
                    $balance += (float)$L->debit - (float)$L->credit;
                    yield [
                        $acc->code.' - '.$acc->name,
                        $L->date, $L->asiento, $L->description,
                        (float)$L->debit, (float)$L->credit, (float)$balance
                    ];
                }
            }
        );
    }

    /* ------------------- Inventario ------------------- */
    public function inventarioCsv(Request $r)
    {
        $wh = $r->input('warehouse_id');
        $q  = trim((string)$r->input('q'));

        $rows = DB::table('product_stocks as ps')
            ->join('products as p','p.id','=','ps.product_id')
            ->join('warehouses as w','w.id','=','ps.warehouse_id')
            ->when($wh, fn($t)=>$t->where('ps.warehouse_id',$wh))
            ->when($q, function($t) use ($q){
                $like = "%{$q}%";
                $t->where(fn($t)=>$t->where('p.sku','like',$like)->orWhere('p.name','like',$like));
            })
            ->select('p.sku','p.name as product','w.code as wh','ps.qty','p.avg_cost')
            ->selectRaw('(ps.qty * p.avg_cost) as value')
            ->orderBy('p.sku')->orderBy('w.code')
            ->get();

        return $this->streamCsv("InventarioValorizado.csv",
            ['SKU','Producto','Bodega','Qty','CostoProm','Valor'],
            function() use ($rows) {
                foreach ($rows as $r) yield [
                    $r->sku, $r->product, $r->wh, (float)$r->qty, (float)$r->avg_cost, (float)$r->value
                ];
            }
        );
    }

    /* ------------------- Kardex ------------------- */
    public function kardexCsv(Request $r)
    {
        $desde = $r->input('desde', date('Y-m-01'));
        $hasta = $r->input('hasta', date('Y-m-t'));
        $pid   = $r->input('product_id');
        $wh    = $r->input('warehouse_id');

        abort_if(!$pid, 400, 'Falta product_id');

        $prod = DB::table('products')->where('id',$pid)->first();

        $openQ = DB::table('kardex')->when($wh,fn($t)=>$t->where('warehouse_id',$wh))
            ->where('product_id',$pid)->where('occurred_at','<',$desde.' 00:00:00')
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type='IN' THEN qty ELSE -qty END),0) q")->value('q');

        $openV = DB::table('kardex')->when($wh,fn($t)=>$t->where('warehouse_id',$wh))
            ->where('product_id',$pid)->where('occurred_at','<',$desde.' 00:00:00')
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type='IN' THEN qty*unit_cost ELSE -qty*unit_cost END),0) v")->value('v');

        $lines = DB::table('kardex as k')
            ->join('warehouses as w','w.id','=','k.warehouse_id')
            ->where('k.product_id',$pid)->when($wh,fn($t)=>$t->where('k.warehouse_id',$wh))
            ->whereBetween('k.occurred_at', [$desde.' 00:00:00',$hasta.' 23:59:59'])
            ->orderBy('k.occurred_at')->orderBy('k.id')
            ->select('k.occurred_at','w.code as wh','k.movement_type','k.qty','k.unit_cost','k.ref_type','k.ref_id')
            ->get();

        $balQ = (float)$openQ; $balV = (float)$openV;

        return $this->streamCsv("Kardex_{$prod->sku}_{$desde}_{$hasta}.csv",
            ['Fecha','Bodega','Tipo','Qty','Costo','Valor','Ref','SaldoQty','SaldoValor'],
            function() use ($lines,&$balQ,&$balV) {
                foreach ($lines as $L) {
                    $val = (float)$L->qty * (float)$L->unit_cost;
                    if ($L->movement_type === 'IN') { $balQ += $L->qty; $balV += $val; }
                    else { $balQ -= $L->qty; $balV -= $val; }

                    yield [
                        $L->occurred_at, $L->wh, $L->movement_type,
                        (float)$L->qty, (float)$L->unit_cost, (float)$val,
                        $L->ref_type.':'.$L->ref_id,
                        (float)$balQ, (float)$balV
                    ];
                }
            }
        );
    }

    /* ------------------- CxC ------------------- */
    public function cxcCsv(Request $r)
    {
        $desde = $r->input('desde', date('Y-m-01'));
        $hasta = $r->input('hasta', date('Y-m-t'));
        $customer = $r->input('customer_id');

        $ventas = DB::table('sales as s')
            ->join('customers as c','c.id','=','s.customer_id')
            ->select('s.id','s.date','c.name as customer','s.total','s.payment_term')
            ->where('s.payment_term','CREDITO')
            ->whereBetween('s.date',[$desde,$hasta])
            ->when($customer, fn($q)=>$q->where('s.customer_id',$customer))
            ->get();

        $abonos = DB::table('receipts')->select('sale_id', DB::raw('SUM(amount) pagado'))
            ->groupBy('sale_id')->pluck('pagado','sale_id');

        return $this->streamCsv("CxC_{$desde}_{$hasta}.csv",
            ['Fecha','Venta','Cliente','Total','Pagado','Saldo'],
            function() use ($ventas,$abonos) {
                foreach ($ventas as $v) {
                    $pagado = (float)($abonos[$v->id] ?? 0);
                    $saldo  = (float)$v->total - $pagado;
                    if ($saldo > 0.01) {
                        yield [$v->date,'VTA-'.$v->id,$v->customer,(float)$v->total,$pagado,$saldo];
                    }
                }
            }
        );
    }

    /* ------------------- CxP ------------------- */
    public function cxpCsv(Request $r)
    {
        $desde = $r->input('desde', date('Y-m-01'));
        $hasta = $r->input('hasta', date('Y-m-t'));
        $supplier = $r->input('supplier_id');

        $compras = DB::table('purchases as p')
            ->join('suppliers as s','s.id','=','p.supplier_id')
            ->select('p.id','p.date','s.name as supplier','p.total','p.payment_term')
            ->where('p.payment_term','CREDITO')
            ->whereBetween('p.date',[$desde,$hasta])
            ->when($supplier, fn($q)=>$q->where('p.supplier_id',$supplier))
            ->get();

        $pagos = DB::table('payments')->select('purchase_id', DB::raw('SUM(amount) pagado'))
            ->groupBy('purchase_id')->pluck('pagado','purchase_id');

        return $this->streamCsv("CxP_{$desde}_{$hasta}.csv",
            ['Fecha','Compra','Proveedor','Total','Pagado','Saldo'],
            function() use ($compras,$pagos) {
                foreach ($compras as $p) {
                    $pagado = (float)($pagos[$p->id] ?? 0);
                    $saldo  = (float)$p->total - $pagado;
                    if ($saldo > 0.01) {
                        yield [$p->date,'CPA-'.$p->id,$p->supplier,(float)$p->total,$pagado,$saldo];
                    }
                }
            }
        );
    }
}




