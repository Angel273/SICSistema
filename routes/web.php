<?php

use Illuminate\Support\Facades\Route;
use Illuminate\support\Facades\DB;
use App\Http\Controllers\DemoController;

use App\Http\Controllers\{PaymentController,ReceiptController,DashboardController,ProductController,CustomerController,SupplierController,StoreController,WarehouseController,PurchaseController,SaleController,ReportController};

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

// Reportes
Route::get('/reports/diario', [ReportController::class,'diario'])->name('reports.diario');
Route::get('/reports/mayor', [ReportController::class,'mayor'])->name('reports.mayor');
Route::get('/reports/ventas',  [ReportController::class,'libroVentas'])->name('reports.ventas');
Route::get('/reports/compras', [ReportController::class,'libroCompras'])->name('reports.compras');
Route::get('/reports/inventario', [ReportController::class,'inventario'])->name('reports.inventario');
Route::get('/reports/kardex',      [ReportController::class,'kardex'])->name('reports.kardex');
Route::get('/reports/cxc', [ReportController::class,'cxc'])->name('reports.cxc');
Route::get('/reports/cxp', [ReportController::class,'cxp'])->name('reports.cxp');


Route::get('/demo', [DemoController::class, 'index']);

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
