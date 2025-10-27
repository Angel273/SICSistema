<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        // Filtros de fecha (por defecto, mes actual)
        $desde = $r->input('desde', date('Y-m-01'));
        $hasta = $r->input('hasta', date('Y-m-t'));

        // KPIs rápidos
        $kpis = [
            'stores'     => DB::table('stores')->count(),
            'warehouses' => DB::table('warehouses')->count(),
            'products'   => DB::table('products')->count(),
            'customers'  => DB::table('customers')->count(),
            'suppliers'  => DB::table('suppliers')->count(),
        ];

        // Ventas y compras del periodo
        $ventas = DB::table('sales')
            ->selectRaw('COUNT(*) as cnt, ROUND(SUM(subtotal),2) as subtotal, ROUND(SUM(tax),2) as tax, ROUND(SUM(total),2) as total')
            ->whereBetween('date', [$desde, $hasta])->first();

        $compras = DB::table('purchases')
            ->selectRaw('COUNT(*) as cnt, ROUND(SUM(subtotal),2) as subtotal, ROUND(SUM(tax),2) as tax, ROUND(SUM(total),2) as total')
            ->whereBetween('date', [$desde, $hasta])->first();

        // Valor de inventario (qty * avg_cost)
        $inventario = DB::table('product_stocks as ps')
            ->join('products as p','p.id','=','ps.product_id')
            ->selectRaw('ROUND(SUM(ps.qty * p.avg_cost),2) as valor, ROUND(SUM(ps.qty),3) as unidades')
            ->first();

        // Saldo "rápido" de Caja y Bancos (suma de mayor)
        $saldoCaja = $this->saldoCuenta('1101'); // Caja
        $saldoBcos = $this->saldoCuenta('1102'); // Bancos

        // Últimos asientos con balance
        $asientos = DB::table('journal_entries as je')
            ->join('journal_lines as jl','jl.journal_entry_id','=','je.id')
            ->select('je.id','je.date','je.description')
            ->selectRaw('ROUND(SUM(jl.debit),2) as debe')
            ->selectRaw('ROUND(SUM(jl.credit),2) as haber')
            ->groupBy('je.id','je.date','je.description')
            ->orderByDesc('je.id')->limit(5)->get();

        // Top 5 productos con más stock
        $stockTop = DB::table('product_stocks as ps')
            ->join('products as p','p.id','=','ps.product_id')
            ->join('warehouses as w','w.id','=','ps.warehouse_id')
            ->select('p.sku','p.name','w.code as warehouse','ps.qty')
            ->orderByDesc('ps.qty')->limit(5)->get();

        // Top 5 vendidos en el periodo (por cantidad)
        $topVendidos = DB::table('sale_items as si')
            ->join('sales as s','s.id','=','si.sale_id')
            ->join('products as p','p.id','=','si.product_id')
            ->whereBetween('s.date',[$desde,$hasta])
            ->select('p.sku','p.name')
            ->selectRaw('SUM(si.qty) as qty')
            ->selectRaw('ROUND(SUM(si.line_total),2) as monto')
            ->groupBy('p.sku','p.name')
            ->orderByDesc('qty')->limit(5)->get();

        // Últimos movimientos de Kardex
        $kardex = DB::table('kardex as k')
            ->join('products as p','p.id','=','k.product_id')
            ->join('warehouses as w','w.id','=','k.warehouse_id')
            ->select('k.occurred_at','p.sku','p.name','w.code as warehouse','k.movement_type','k.qty','k.unit_cost','k.ref_type','k.ref_id')
            ->orderByDesc('k.id')->limit(8)->get();

        return view('dashboard', compact(
            'desde','hasta','kpis','ventas','compras','inventario','saldoCaja','saldoBcos','asientos','stockTop','topVendidos','kardex'
        ));
    }

    private function saldoCuenta(string $code): float
    {
        $accId = DB::table('accounts')->where('code',$code)->value('id');
        if(!$accId) return 0;
        $row = DB::table('journal_lines')->selectRaw('SUM(debit) d, SUM(credit) c')
              ->where('account_id',$accId)->first();
        return round(($row->d ?? 0) - ($row->c ?? 0), 2);
    }
}
