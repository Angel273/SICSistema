<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function create()
    {
        return view('sales.create', [
            'customers' => DB::table('customers')->orderBy('name')->get(),
            'stores'    => DB::table('stores')->orderBy('name')->get(),
            'products'  => DB::table('products')->select('id','sku','name','avg_cost')->orderBy('sku')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'customer_id'  => 'required|integer',
            'store_id'     => 'required|integer',
            'date'         => 'required|date',
            'payment_term' => 'required|in:CONTADO,CREDITO',
            'due_date'     => 'nullable|date',
            'items'        => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.qty'        => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount'   => 'nullable|numeric|min:0',
            'items.*.tax_rate'   => 'nullable|numeric|min:0'
        ]);

        return DB::transaction(function () use ($r) {

            // 1) Totales de venta + COGS (al costo promedio actual)
            $subtotal = 0; $tax = 0; $total = 0; $cogs = 0;

            // Traemos costos promedio por producto de una vez
            $avgCosts = DB::table('products')
                ->whereIn('id', collect($r->items)->pluck('product_id'))
                ->pluck('avg_cost','id');

            foreach ($r->items as $it) {
                $qty   = (float)$it['qty'];
                $price = (float)$it['unit_price'];
                $disc  = (float)($it['discount'] ?? 0);
                $rate  = (float)($it['tax_rate']  ?? 13);

                // subtotal línea (precio - descuento) * qty
                $netUnit = max(0,$price - $disc);
                $lineSub = $qty * $netUnit;
                $lineTax = round($lineSub * ($rate/100), 2);

                $subtotal += $lineSub; $tax += $lineTax; $total += $lineSub + $lineTax;

                // COGS por línea (qty * avg_cost vigente)
                $avg = (float)($avgCosts[$it['product_id']] ?? 0);
                $cogs += $qty * $avg;
            }

            // 2) Encabezado de la venta
            $saleId = DB::table('sales')->insertGetId([
                'customer_id'  => $r->customer_id,
                'store_id'     => $r->store_id,
                'date'         => $r->date,
                'payment_term' => $r->payment_term,
                'due_date'     => $r->payment_term === 'CREDITO' ? ($r->due_date ?: $r->date) : null,
                'subtotal'     => $subtotal,
                'tax'          => $tax,
                'total'        => $total,
            ]);

            // Necesitamos la bodega por donde sale mercadería (simple: la 1ra del store)
            $warehouseId = DB::table('warehouses')->where('store_id',$r->store_id)->value('id');
            if(!$warehouseId){ throw new \RuntimeException("La tienda seleccionada no tiene bodega asociada."); }

            // 3) Ítems + Kardex OUT + actualización de stock (con verificación)
            foreach ($r->items as $it) {
                $qty   = (float)$it['qty'];
                $price = (float)$it['unit_price'];
                $disc  = (float)($it['discount'] ?? 0);
                $rate  = (float)($it['tax_rate'] ?? 13);

                $netUnit = max(0,$price - $disc);
                $lineSub = $qty * $netUnit;
                $lineTax = round($lineSub * ($rate/100), 2);

                DB::table('sale_items')->insert([
                    'sale_id'       => $saleId,
                    'product_id'    => $it['product_id'],
                    'qty'           => $qty,
                    'unit_price'    => $price,
                    'discount'      => $disc,
                    'tax_rate'      => $rate,
                    'line_subtotal' => $lineSub,
                    'line_tax'      => $lineTax,
                    'line_total'    => $lineSub + $lineTax,
                ]);

                // Verificar stock suficiente
                $onHand = (float) DB::table('product_stocks')
                    ->where(['product_id'=>$it['product_id'],'warehouse_id'=>$warehouseId])
                    ->value('qty') ?? 0;
                if ($onHand < $qty) {
                    throw new \RuntimeException("Stock insuficiente para producto ID {$it['product_id']} (disponible: {$onHand}).");
                }

                // Kardex OUT al costo promedio vigente
                $avg = (float)($avgCosts[$it['product_id']] ?? 0);
                DB::table('kardex')->insert([
                    'product_id'   => $it['product_id'],
                    'warehouse_id' => $warehouseId,
                    'movement_type'=> 'OUT',
                    'qty'          => $qty,
                    'unit_cost'    => $avg,
                    'ref_type'     => 'sale',
                    'ref_id'       => $saleId,
                    'occurred_at'  => now(),
                ]);

                // Descontar stock
                DB::table('product_stocks')
                    ->where(['product_id'=>$it['product_id'],'warehouse_id'=>$warehouseId])
                    ->update(['qty'=>DB::raw('qty - '.$qty)]);
            }

            // 4) Asiento contable
            $jeId = DB::table('journal_entries')->insertGetId([
                'date'        => $r->date,
                'description' => "Venta #$saleId (".$r->payment_term.")",
                'ref_type'    => 'sale',
                'ref_id'      => $saleId,
            ]);

            $accCaja = DB::table('accounts')->where('code','1101')->value('id'); // Caja
            $accCxC  = DB::table('accounts')->where('code','1201')->value('id'); // Clientes
            $accVtas = DB::table('accounts')->where('code','5101')->value('id'); // Ventas
            $accIvaD = DB::table('accounts')->where('code','2106')->value('id'); // IVA Débito
            $accCOGS = DB::table('accounts')->where('code','4101')->value('id'); // Costo de Ventas
            $accInv  = DB::table('accounts')->where('code','1109')->value('id'); // Inventario

            // (A) Ingreso + IVA + Cobro o CxC
            if($r->payment_term === 'CONTADO'){
                DB::table('journal_lines')->insert(['journal_entry_id'=>$jeId,'account_id'=>$accCaja,'debit'=>$total,'credit'=>0]);
            } else {
                DB::table('journal_lines')->insert(['journal_entry_id'=>$jeId,'account_id'=>$accCxC,'debit'=>$total,'credit'=>0]);
            }
            DB::table('journal_lines')->insert([
                ['journal_entry_id'=>$jeId,'account_id'=>$accVtas,'debit'=>0,'credit'=>$subtotal],
                ['journal_entry_id'=>$jeId,'account_id'=>$accIvaD,'debit'=>0,'credit'=>$tax],
            ]);

            // (B) COGS vs Inventario
            $cogs = round($cogs,2);
            DB::table('journal_lines')->insert([
                ['journal_entry_id'=>$jeId,'account_id'=>$accCOGS,'debit'=>$cogs,'credit'=>0],
                ['journal_entry_id'=>$jeId,'account_id'=>$accInv ,'debit'=>0,'credit'=>$cogs],
            ]);

            // 5) Done
            return redirect()->route('dashboard')->with('ok', "Venta #$saleId creada");
        });
    }
}
