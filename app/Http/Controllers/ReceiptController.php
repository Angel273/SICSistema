<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceiptController extends Controller
{
     public function create(Request $r)
    {
        // Catálogos
        $customers = DB::table('customers')->orderBy('name')->get();

        // Cuentas de Caja/Bancos (ajusta códigos si usas otros)
        $cashAccounts = DB::table('accounts')
            ->whereIn('code', ['1101','1102']) // 1101 Caja, 1102 Bancos
            ->orderBy('code')->get();

        // Ventas a crédito con saldo pendiente (outstanding > 0)
        $creditSales = DB::table('sales as s')
            ->leftJoin(DB::raw("(SELECT sale_id, SUM(amount) as paid FROM receipts GROUP BY sale_id) r"), 'r.sale_id', '=', 's.id')
            ->join('customers as c','c.id','=','s.customer_id')
            ->select('s.id','s.date','s.total','s.customer_id','c.name as customer_name',
                     DB::raw('ROUND(s.total - IFNULL(r.paid,0), 2) as outstanding'))
            ->where('s.payment_term','CREDITO')
            ->having('outstanding', '>', 0)
            ->orderByDesc('s.id')
            ->get();

        return view('receipts.create', compact('customers','cashAccounts','creditSales'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'customer_id'               => 'required|integer',
            'account_id_cash_or_bank'   => 'required|integer',
            'date'                      => 'required|date',
            'amount'                    => 'required|numeric|min:0.01',
            'sale_id'                   => 'nullable|integer',
            'notes'                     => 'nullable|string|max:255'
        ]);

        return DB::transaction(function() use ($r){
            $amount = (float)$r->amount;

            // Si se ligó a una venta, verificar saldo pendiente y capear monto
            $saleId = $r->sale_id ?: null;
            if ($saleId) {
                $sale = DB::table('sales')->where('id',$saleId)->first(['id','customer_id','total','payment_term']);
                if (!$sale) throw new \RuntimeException("Venta no encontrada.");
                if ((int)$sale->customer_id !== (int)$r->customer_id) {
                    throw new \RuntimeException("La venta no pertenece al cliente seleccionado.");
                }
                if ($sale->payment_term !== 'CREDITO') {
                    throw new \RuntimeException("La venta seleccionada no es a crédito.");
                }
                $paid = (float) DB::table('receipts')->where('sale_id',$saleId)->sum('amount');
                $outstanding = round((float)$sale->total - $paid, 2);
                if ($outstanding <= 0) {
                    throw new \RuntimeException("La venta ya está saldada.");
                }
                // Evitar sobrepago
                if ($amount > $outstanding) $amount = $outstanding;
            }

            // 1) Insertar recibo
            $receiptId = DB::table('receipts')->insertGetId([
                'customer_id'             => $r->customer_id,
                'sale_id'                 => $saleId,
                'account_id_cash_or_bank' => $r->account_id_cash_or_bank,
                'date'                    => $r->date,
                'amount'                  => $amount,
               // 'notes'                   => $r->notes,
            ]);

            // 2) Asiento contable: Debe Caja/Banco, Haber CxC
            $jeId = DB::table('journal_entries')->insertGetId([
                'date'        => $r->date,
                'description' => "Cobro #$receiptId",
                'ref_type'    => 'receipt',
                'ref_id'      => $receiptId,
            ]);

            $accCxC = DB::table('accounts')->where('code','1201')->value('id'); // Clientes (CxC)
            if(!$accCxC) throw new \RuntimeException("Cuenta CxC (1201) no existe.");
            DB::table('journal_lines')->insert([
                ['journal_entry_id'=>$jeId,'account_id'=>$r->account_id_cash_or_bank,'debit'=>$amount,'credit'=>0],
                ['journal_entry_id'=>$jeId,'account_id'=>$accCxC,'debit'=>0,'credit'=>$amount],
            ]);

            // 3) Redirigir al Dashboard
            return redirect()->route('dashboard')->with('ok', "Cobro #$receiptId registrado");
        });
    }
}
