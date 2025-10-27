<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DemoController extends Controller
{
    public function index()
    {
        // Totales rápidos
        $totals = DB::table('information_schema.tables')
            ->selectRaw('(SELECT COUNT(*) FROM stores) AS stores')
            ->selectRaw('(SELECT COUNT(*) FROM warehouses) AS warehouses')
            ->selectRaw('(SELECT COUNT(*) FROM products) AS products')
            ->selectRaw('(SELECT COUNT(*) FROM customers) AS customers')
            ->selectRaw('(SELECT COUNT(*) FROM suppliers) AS suppliers')
            ->selectRaw('(SELECT COUNT(*) FROM purchases) AS purchases')
            ->selectRaw('(SELECT COUNT(*) FROM sales) AS sales')
            ->selectRaw('(SELECT COUNT(*) FROM kardex) AS kardex')
            ->selectRaw('(SELECT COUNT(*) FROM journal_entries) AS journal_entries')
            ->first();

        // Top 5 productos con stock
        $stock = DB::table('product_stocks AS ps')
            ->join('products AS p','p.id','=','ps.product_id')
            ->join('warehouses AS w','w.id','=','ps.warehouse_id')
            ->select('p.sku','p.name','w.code AS warehouse','ps.qty')
            ->orderByDesc('ps.qty')
            ->limit(5)
            ->get();

        // Últimas 5 compras y ventas
        $purchases = DB::table('purchases')->orderByDesc('id')->limit(5)->get();
        $sales     = DB::table('sales')->orderByDesc('id')->limit(5)->get();

        // Últimos 3 asientos con su total debe/haber
        $entries = DB::table('journal_entries AS je')
            ->join('journal_lines AS jl','jl.journal_entry_id','=','je.id')
            ->select('je.id','je.date','je.description')
            ->selectRaw('ROUND(SUM(jl.debit),2) AS debe')
            ->selectRaw('ROUND(SUM(jl.credit),2) AS haber')
            ->groupBy('je.id','je.date','je.description')
            ->orderByDesc('je.id')
            ->limit(3)
            ->get();

        return view('demo.index', compact('totals','stock','purchases','sales','entries'));
    }
}
