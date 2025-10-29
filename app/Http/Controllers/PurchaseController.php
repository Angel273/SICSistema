<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function create()
    {
        return view('purchases.create', [
            'suppliers'  => DB::table('suppliers')->orderBy('name')->get(),
            'warehouses' => DB::table('warehouses')->orderBy('name')->get(),
            'products'   => DB::table('products')->select('id','sku','name','avg_cost')->orderBy('sku')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'supplier_id'  => 'required|integer',
            'warehouse_id' => 'required|integer',
            'date'         => 'required|date',
            'payment_term' => 'required|in:CONTADO,CREDITO',
            'due_date'     => 'nullable|date',
            'items'        => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.qty'        => 'required|numeric|min:0.001',
            'items.*.unit_cost'  => 'required|numeric|min:0',
            'items.*.tax_rate'   => 'nullable|numeric|min:0'
        ]);

        return DB::transaction(function () use ($r) {

            // 1) Totales
            $subtotal = 0.0; $tax = 0.0;
            foreach ($r->items as $it) {
                $qty   = (float)$it['qty'];
                $ucost = (float)$it['unit_cost'];
                $rate  = (float)($it['tax_rate'] ?? 13);
                $lineSub = $qty * $ucost;
                $lineTax = round($lineSub * ($rate/100), 2);
                $subtotal += $lineSub;
                $tax      += $lineTax;
            }
            $subtotal = round($subtotal, 2);
            $tax      = round($tax, 2);
            $total    = round($subtotal + $tax, 2);

            // 2) Encabezado
            $purchaseId = DB::table('purchases')->insertGetId([
                'supplier_id'  => $r->supplier_id,
                'warehouse_id' => $r->warehouse_id,
                'date'         => $r->date,
                'payment_term' => $r->payment_term,
                'due_date'     => $r->payment_term === 'CREDITO' ? ($r->due_date ?: $r->date) : null,
                'subtotal'     => $subtotal,
                'tax'          => $tax,
                'total'        => $total,
            ]);

            // 3) Items + Kardex + Stock + Costo promedio
            foreach ($r->items as $it) {
                $productId = (int)$it['product_id'];
                $qty       = (float)$it['qty'];
                $ucost     = (float)$it['unit_cost'];
                $rate      = (float)($it['tax_rate'] ?? 13);

                $lineSub = round($qty * $ucost, 2);
                $lineTax = round($lineSub * ($rate/100), 2);

                DB::table('purchase_items')->insert([
                    'purchase_id'   => $purchaseId,
                    'product_id'    => $productId,
                    'qty'           => $qty,
                    'unit_cost'     => $ucost,
                    'tax_rate'      => $rate,
                    'line_subtotal' => $lineSub,
                    'line_tax'      => $lineTax,
                    'line_total'    => round($lineSub + $lineTax, 2),
                ]);

                // Kardex IN (usar fecha de la compra)
                DB::table('kardex')->insert([
                    'product_id'    => $productId,
                    'warehouse_id'  => $r->warehouse_id,
                    'movement_type' => 'IN',
                    'qty'           => $qty,
                    'unit_cost'     => $ucost,
                    'ref_type'      => 'purchase',
                    'ref_id'        => $purchaseId,
                    'occurred_at'   => $r->date.' 00:00:00',
                ]);

                // Stock (upsert + increment)
                DB::table('product_stocks')->updateOrInsert(
                    ['product_id' => $productId, 'warehouse_id' => $r->warehouse_id],
                    ['qty' => DB::raw('COALESCE(qty,0) + '.($qty))]
                );

                // Recalcular costo promedio ponderado
                $p = DB::table('products')->where('id',$productId)->first(['avg_cost']);
                $onHand = (float) DB::table('product_stocks')
                    ->where(['product_id'=>$productId, 'warehouse_id'=>$r->warehouse_id])
                    ->value('qty');

                $prevQty = max(0.0, $onHand - $qty);
                $prevVal = $prevQty * (float)$p->avg_cost;
                $newVal  = $qty * $ucost;
                $newAvg  = ($prevVal + $newVal) / max(1e-9, ($prevQty + $qty));

                DB::table('products')->where('id',$productId)->update(['avg_cost'=>round($newAvg,2)]);
            }

            // 4) Asiento contable
            $jeId = DB::table('journal_entries')->insertGetId([
                'date'        => $r->date,
                'description' => "Compra #$purchaseId (".$r->payment_term.")",
                'ref_type'    => 'purchase',
                'ref_id'      => $purchaseId,
            ]);

            $accInv  = DB::table('accounts')->where('code','1109')->value('id'); // Inventario
            $accIvc  = DB::table('accounts')->where('code','1106')->value('id'); // IVA Crédito
            $accCxp  = DB::table('accounts')->where('code','2102')->value('id'); // CxP
            $accCaja = DB::table('accounts')->where('code','1101')->value('id'); // Caja

            DB::table('journal_lines')->insert([
                ['journal_entry_id'=>$jeId,'account_id'=>$accInv,'debit'=>$subtotal,'credit'=>0],
                ['journal_entry_id'=>$jeId,'account_id'=>$accIvc,'debit'=>$tax,'credit'=>0],
            ]);

            if ($r->payment_term === 'CREDITO') {
                DB::table('journal_lines')->insert([
                    'journal_entry_id'=>$jeId,'account_id'=>$accCxp,'debit'=>0,'credit'=>$total
                ]);
            } else {
                DB::table('journal_lines')->insert([
                    'journal_entry_id'=>$jeId,'account_id'=>$accCaja,'debit'=>0,'credit'=>$total
                ]);
            }

            // 5) Redirect OK
            return redirect()->route('dashboard')->with('ok', "Compra #$purchaseId creada");
        });
    }
}
