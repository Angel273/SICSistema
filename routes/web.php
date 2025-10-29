<?php

use Illuminate\Support\Facades\Route;
use Illuminate\support\Facades\DB;
use App\Http\Controllers\DemoController;

use App\Http\Controllers\{AccountController,ExportController,PaymentController,ReceiptController,DashboardController,ProductController,CustomerController,SupplierController,StoreController,WarehouseController,PurchaseController,SaleController,ReportController};

Route::get('/', fn() => redirect()->route('dashboard'));
Route::get('/dashboard',[DashboardController::class, 'index'])->name('dashboard');
Route::resource('warehouses', WarehouseController::class)->except(['show']);
Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
Route::post('/purchases',        [PurchaseController::class, 'store'])->name('purchases.store');
Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
Route::post('/sales',        [SaleController::class, 'store'])->name('sales.store');
Route::resource('products', ProductController::class)->except(['show']);
Route::resource('customers', CustomerController::class)->except(['show']);
Route::resource('suppliers', SupplierController::class)->except(['show']);
Route::resource('stores', StoreController::class)->except(['show']);
Route::get('/receipts/create', [ReceiptController::class, 'create'])->name('receipts.create');
Route::post('/receipts',        [ReceiptController::class, 'store'])->name('receipts.store');
Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
Route::post('/payments',        [PaymentController::class, 'store'])->name('payments.store');
Route::get('customers-master', [\App\Http\Controllers\CustomerMasterController::class,'index'])->name('customers.master.index');
Route::get('customers-master/{id}', [\App\Http\Controllers\CustomerMasterController::class,'show'])->name('customers.master.show');
Route::get('/accounts',            [AccountController::class,'index'])->name('accounts.index');
Route::get('/accounts/create',     [AccountController::class,'create'])->name('accounts.create');
Route::post('/accounts',           [AccountController::class,'store'])->name('accounts.store');
Route::get('/accounts/{id}/edit',  [AccountController::class,'edit'])->name('accounts.edit');
Route::put('/accounts/{id}',       [AccountController::class,'update'])->name('accounts.update');
Route::delete('/accounts/{id}',    [AccountController::class,'destroy'])->name('accounts.destroy');



// Reportes
Route::get('/reports/diario', [ReportController::class,'diario'])->name('reports.diario');
Route::get('/reports/mayor', [ReportController::class,'mayor'])->name('reports.mayor');
Route::get('/reports/ventas',  [ReportController::class,'libroVentas'])->name('reports.ventas');
Route::get('/reports/compras', [ReportController::class,'libroCompras'])->name('reports.compras');
Route::get('/reports/inventario', [ReportController::class,'inventario'])->name('reports.inventario');
Route::get('/reports/kardex',      [ReportController::class,'kardex'])->name('reports.kardex');
Route::get('/reports/cxc', [ReportController::class,'cxc'])->name('reports.cxc');
Route::get('/reports/cxp', [ReportController::class,'cxp'])->name('reports.cxp');

//exporte de csv
Route::get('/exports/ventas/csv',     [ExportController::class,'ventasCsv'])->name('exports.ventas.csv');
Route::get('/exports/compras/csv',    [ExportController::class,'comprasCsv'])->name('exports.compras.csv');
Route::get('/exports/diario/csv',     [ExportController::class,'diarioCsv'])->name('exports.diario.csv');
Route::get('/exports/mayor/csv',      [ExportController::class,'mayorCsv'])->name('exports.mayor.csv');
Route::get('/exports/inventario/csv', [ExportController::class,'inventarioCsv'])->name('exports.inventario.csv');
Route::get('/exports/kardex/csv',     [ExportController::class,'kardexCsv'])->name('exports.kardex.csv');
Route::get('/exports/cxc/csv',        [ExportController::class,'cxcCsv'])->name('exports.cxc.csv');
Route::get('/exports/cxp/csv',        [ExportController::class,'cxpCsv'])->name('exports.cxp.csv');


//exporte de pdf
Route::get('/exports/ventas/pdf',     [ExportController::class,'ventasPdf'])->name('exports.ventas.pdf');
Route::get('/exports/compras/pdf',    [ExportController::class,'comprasPdf'])->name('exports.compras.pdf');
Route::get('/exports/diario/pdf',     [ExportController::class,'diarioPdf'])->name('exports.diario.pdf');
Route::get('/exports/mayor/pdf',      [ExportController::class,'mayorPdf'])->name('exports.mayor.pdf');
Route::get('/exports/inventario/pdf', [ExportController::class,'inventarioPdf'])->name('exports.inventario.pdf');
Route::get('/exports/kardex/pdf',     [ExportController::class,'kardexPdf'])->name('exports.kardex.pdf');
Route::get('/exports/cxc/pdf',        [ExportController::class,'cxcPdf'])->name('exports.cxc.pdf');
Route::get('/exports/cxp/pdf',        [ExportController::class,'cxpPdf'])->name('exports.cxp.pdf');


Route::get('/demo', [DemoController::class, 'index']);


Route::get('/exports/ventas/csv', function () {
    $desde = request('desde', date('Y-m-01'));
    $hasta = request('hasta', date('Y-m-t'));

    $rows = DB::table('sales as s')
        ->join('customers as c','c.id','=','s.customer_id')
        ->select('s.date','s.id','c.name as customer','s.subtotal','s.tax','s.total')
        ->whereBetween('s.date', [$desde, $hasta])
        ->orderBy('s.date')->orderBy('s.id')
        ->get();

    $filename = "LibroVentas_{$desde}_{$hasta}.csv";
    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function() use ($rows) {
        $out = fopen('php://output', 'w');
        // BOM para que Excel detecte UTF-8
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, ['Fecha','Doc','Cliente','Gravado','IVA','Total']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r->date,
                'VTA-'.$r->id,
                $r->customer,
                number_format((float)$r->subtotal,2,'.',''),
                number_format((float)$r->tax,2,'.',''),
                number_format((float)$r->total,2,'.',''),
            ]);
        }
        fclose($out);
    };

    return response()->stream($callback, 200, $headers);
})->name('exports.ventas.csv');


Route::get('/health/db', function () {
    try {
        DB::connection()->getPdo(); // abre conexión
        $version = DB::selectOne('SELECT VERSION() AS v')->v ?? 'desconocida';
        $dbName  = DB::getDatabaseName();
        return "OK Conectado a MySQL {$version}, BD: {$dbName}";
    } catch (\Throwable $e) {
        return response(" Error de conexión: ".$e->getMessage(), 500);
    }
});

Route::fallback(function () {
    return response()->view('errors.404', ['message' => 'Ruta no encontrada'], 404);
});
