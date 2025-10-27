<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PaymentController extends Controller
{
     public function create(Request $r)
    {
        $suppliers = DB::table('suppliers')->orderBy('name')->get();

        // Cuentas de salida de dinero (Caja/Bancos)
        $cashAccounts = DB::table('accounts')
            ->whereIn('code', ['1101','1102']) // ajusta si usas otras
            ->orderBy('code')->get();

        // Compras a crédito con saldo pendiente
        $creditPurchases = DB::table('purchases as p')
            ->leftJoin(DB::raw("(SELECT purchase_id, SUM(amount) as paid FROM payments GROUP BY purchase_id) pay"), 'pay.purchase_id','=','p.id')
            ->join('suppliers as s','s.id','=','p.supplier_id')
            ->select('p.id','p.date','p.total','p.supplier_id','s.name as supplier_name',
                     DB::raw('ROUND(p.total - IFNULL(pay.paid,0),2) as outstanding'))
            ->where('p.payment_term','CREDITO')
            ->having('outstanding','>',0)
            ->orderByDesc('p.id')
            ->get();

        return view('payments.create', compact('suppliers','cashAccounts','creditPurchases'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'supplier_id'             => 'required|integer',
            'account_id_cash_or_bank' => 'required|integer',
            'date'                    => 'required|date',
            'amount'                  => 'required|numeric|min:0.01',
            'purchase_id'             => 'nullable|integer',
            'notes'                   => 'nullable|string|max:255'
        ]);

        return DB::transaction(function() use ($r){
            $amount = (float)$r->amount;
            $purchaseId = $r->purchase_id ?: null;

            if ($purchaseId) {
                $p = DB::table('purchases')->where('id',$purchaseId)->first(['id','supplier_id','total','payment_term']);
                if (!$p) throw new \RuntimeException("Compra no encontrada.");
                if ((int)$p->supplier_id !== (int)$r->supplier_id) {
                    throw new \RuntimeException("La compra no pertenece al proveedor seleccionado.");
                }
                if ($p->payment_term !== 'CREDITO') {
                    throw new \RuntimeException("La compra seleccionada no es a crédito.");
                }
                $paid = (float) DB::table('payments')->where('purchase_id',$purchaseId)->sum('amount');
                $outstanding = round((float)$p->total - $paid, 2);
                if ($outstanding <= 0) throw new \RuntimeException("La compra ya está saldada.");
                if ($amount > $outstanding) $amount = $outstanding; // evitar sobrepago
            }

            // 1) Insertar pago
            $paymentId = DB::table('payments')->insertGetId([
                'supplier_id'             => $r->supplier_id,
                'purchase_id'             => $purchaseId,
                'account_id_cash_or_bank' => $r->account_id_cash_or_bank,
                'date'                    => $r->date,
                'amount'                  => $amount,
                // 'notes'                 => $r->notes,  // habilita si agregaste la columna
            ]);

            // 2) Asiento contable: Debe CxP, Haber Caja/Bancos
            $jeId = DB::table('journal_entries')->insertGetId([
                'date'        => $r->date,
                'description' => "Pago #$paymentId",
                'ref_type'    => 'payment',
                'ref_id'      => $paymentId,
            ]);

            $accCxp = DB::table('accounts')->where('code','2102')->value('id'); // Proveedores (CxP)
            if(!$accCxp) throw new \RuntimeException("Cuenta CxP (2102) no existe.");

            DB::table('journal_lines')->insert([
                ['journal_entry_id'=>$jeId,'account_id'=>$accCxp,'debit'=>$amount,'credit'=>0],
                ['journal_entry_id'=>$jeId,'account_id'=>$r->account_id_cash_or_bank,'debit'=>0,'credit'=>$amount],
            ]);

            return redirect()->route('dashboard')->with('ok', "Pago #$paymentId registrado");
        });
    }
}
