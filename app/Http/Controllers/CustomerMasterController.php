<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerMasterController extends Controller
{
  // Listado con saldo por cliente (y aging resumido)
  public function index(Request $r){
    $q = $r->get('q');

    // Totales por cliente
    $rows = DB::table('customers as c')
      ->leftJoin('sales as s', function($j){
        $j->on('s.customer_id','=','c.id')->where('s.payment_term','CREDITO');
      })
      ->leftJoin('receipts as rc','rc.customer_id','=','c.id')
      ->selectRaw('c.id, c.name,
        ROUND(IFNULL(SUM(s.total),0),2) as credit_total,
        ROUND(IFNULL(SUM(rc.amount),0),2) as receipts_total')
      ->when($q, fn($qr)=>$qr->where('c.name','like',"%$q%"))
      ->groupBy('c.id','c.name')
      ->orderBy('c.name')
      ->get()
      ->map(function($x){
        $x->outstanding = round(($x->credit_total - $x->receipts_total),2);
        return $x;
      });

    return view('customers.master.index', ['rows'=>$rows, 'q'=>$q]);
  }

  // Detalle de un cliente: facturas abiertas, aging, cobros
  public function show($id){
    $customer = DB::table('customers')->find($id);
    abort_unless($customer,404);

    // Facturas a crédito con saldo y aging por factura
    $invoices = DB::table('sales as s')
      ->leftJoin(DB::raw("(SELECT sale_id, SUM(amount) paid FROM receipts GROUP BY sale_id) r"),'r.sale_id','=','s.id')
      ->selectRaw("s.id, s.date, s.total,
        ROUND(s.total - IFNULL(r.paid,0),2) as outstanding,
        DATEDIFF(CURDATE(), s.date) as days")
      ->where('s.customer_id',$id)
      ->where('s.payment_term','CREDITO')
      ->having('outstanding','>',0)
      ->orderBy('s.date')
      ->get();

    // Aging buckets
    $aging = ['b0_30'=>0,'b31_60'=>0,'b61_90'=>0,'b90p'=>0];
    foreach($invoices as $inv){
      $o = (float)$inv->outstanding;
      $d = (int)$inv->days;
      if($d<=30) $aging['b0_30'] += $o;
      elseif($d<=60) $aging['b31_60'] += $o;
      elseif($d<=90) $aging['b61_90'] += $o;
      else $aging['b90p'] += $o;
    }
    foreach($aging as $k=>$v) $aging[$k] = round($v,2);

    // Últimos cobros
    $receipts = DB::table('receipts as rc')
      ->leftJoin('sales as s','s.id','=','rc.sale_id')
      ->select('rc.id','rc.date','rc.amount','rc.sale_id')
      ->where('rc.customer_id',$id)
      ->orderByDesc('rc.date')->limit(25)->get();

    // Totales
    $totCredit = (float) DB::table('sales')->where(['customer_id'=>$id,'payment_term'=>'CREDITO'])->sum('total');
    $totReceipts = (float) DB::table('receipts')->where('customer_id',$id)->sum('amount');
    $saldo = round($totCredit - $totReceipts, 2);

    return view('customers.master.show', compact('customer','invoices','aging','receipts','saldo','totCredit','totReceipts'));
  }
}
