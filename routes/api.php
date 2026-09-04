<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\CashController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\LayawayController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum')->name('me');

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->middleware('can:products.create')->name('products.store');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('can:products.manage')->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('can:records.delete')->name('products.destroy');

        Route::get('/categories', [CatalogController::class, 'categories'])->name('categories.index');
        Route::post('/categories', [CatalogController::class, 'storeCategory'])->middleware('can:warehouse.manage')->name('categories.store');
        Route::put('/categories/{category}', [CatalogController::class, 'updateCategory'])->middleware('can:warehouse.manage')->name('categories.update');
        Route::delete('/categories/{category}', [CatalogController::class, 'destroyCategory'])->middleware('can:records.delete')->name('categories.destroy');

        Route::get('/brands', [CatalogController::class, 'brands'])->name('brands.index');
        Route::post('/brands', [CatalogController::class, 'storeBrand'])->middleware('can:warehouse.manage')->name('brands.store');
        Route::put('/brands/{brand}', [CatalogController::class, 'updateBrand'])->middleware('can:warehouse.manage')->name('brands.update');
        Route::delete('/brands/{brand}', [CatalogController::class, 'destroyBrand'])->middleware('can:records.delete')->name('brands.destroy');

        Route::get('/presentations', [CatalogController::class, 'presentations'])->name('presentations.index');
        Route::post('/presentations', [CatalogController::class, 'storePresentation'])->middleware('can:warehouse.manage')->name('presentations.store');
        Route::put('/presentations/{presentation}', [CatalogController::class, 'updatePresentation'])->middleware('can:warehouse.manage')->name('presentations.update');
        Route::delete('/presentations/{presentation}', [CatalogController::class, 'destroyPresentation'])->middleware('can:records.delete')->name('presentations.destroy');

        Route::get('/customers', [CatalogController::class, 'customers'])->name('customers.index');
        Route::post('/customers', [CatalogController::class, 'storeCustomer'])->middleware('can:customers.manage')->name('customers.store');
        Route::put('/customers/{customer}', [CatalogController::class, 'updateCustomer'])->middleware('can:customers.manage')->name('customers.update');

        Route::get('/suppliers', [CatalogController::class, 'suppliers'])->name('suppliers.index');
        Route::post('/suppliers', [CatalogController::class, 'storeSupplier'])->middleware('can:purchases.manage')->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [CatalogController::class, 'updateSupplier'])->middleware('can:purchases.manage')->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [CatalogController::class, 'destroySupplier'])->middleware('can:records.delete')->name('suppliers.destroy');

        Route::get('/sales', [SaleController::class, 'index'])->middleware('can:sales.manage')->name('sales.index');
        Route::post('/sales', [SaleController::class, 'store'])->middleware('can:sales.manage')->name('sales.store');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->middleware('can:sales.manage')->name('sales.show');
        Route::post('/sales/{sale}/payments', [SaleController::class, 'payments'])->middleware('can:sales.manage')->name('sales.payments');

        Route::get('/inventory/entries', [InventoryController::class, 'entries'])->middleware('can:inventory.entries.manage')->name('inventory.entries');
        Route::post('/inventory/entries', [InventoryController::class, 'storeEntry'])->middleware('can:inventory.entries.manage')->name('inventory.entries.store');
        Route::get('/inventory/kardex', [InventoryController::class, 'kardex'])->middleware('can:kardex.manage')->name('inventory.kardex');
        Route::get('/inventory/stock', [InventoryController::class, 'stock'])->name('inventory.stock');

        Route::get('/purchases', [PurchaseController::class, 'index'])->middleware('can:purchases.manage')->name('purchases.index');
        Route::post('/purchases', [PurchaseController::class, 'store'])->middleware('can:purchases.manage')->name('purchases.store');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->middleware('can:purchases.manage')->name('purchases.show');

        Route::get('/layaways', [LayawayController::class, 'index'])->middleware('can:layaways.manage')->name('layaways.index');
        Route::post('/layaways', [LayawayController::class, 'store'])->middleware('can:layaways.manage')->name('layaways.store');
        Route::post('/layaways/{layaway}/payments', [LayawayController::class, 'pay'])->middleware('can:layaways.manage')->name('layaways.payments');
        Route::post('/layaways/{layaway}/complete', [LayawayController::class, 'complete'])->middleware('can:layaways.manage')->name('layaways.complete');

        Route::get('/cash', [CashController::class, 'index'])->middleware('can:cash.manage')->name('cash.index');
        Route::post('/cash/open', [CashController::class, 'open'])->middleware('can:cash.manage')->name('cash.open');
        Route::put('/cash/{cashRegister}/close', [CashController::class, 'close'])->middleware('can:cash.manage')->name('cash.close');
        Route::post('/cash/{cashRegister}/movements', [CashController::class, 'movement'])->middleware('can:cash.manage')->name('cash.movements');

        Route::get('/reports/movements', [ReportController::class, 'movements'])->middleware('can:reports.manage')->name('reports.movements');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->middleware('can:reports.manage')->name('reports.sales');
        Route::get('/reports/purchases', [ReportController::class, 'purchases'])->middleware('can:reports.manage')->name('reports.purchases');
        Route::get('/reports/debtors', [ReportController::class, 'debtors'])->middleware('can:reports.manage')->name('reports.debtors');
        Route::get('/reports/catalog', [ReportController::class, 'catalog'])->middleware('can:reports.manage')->name('reports.catalog');
        Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])->middleware('can:reports.manage')->name('reports.low-stock');
        Route::get('/reports/top-selling', [ReportController::class, 'topSelling'])->middleware('can:reports.manage')->name('reports.top-selling');

        Route::get('/users', [UserController::class, 'index'])->middleware('can:users.manage')->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->middleware('can:users.manage')->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->middleware('can:users.manage')->name('users.update');

        Route::post('/backup/create', [BackupController::class, 'create'])->middleware('can:settings.manage')->name('backup.create');
        Route::get('/backup/status', [BackupController::class, 'status'])->middleware('can:settings.manage')->name('backup.status');
        Route::get('/backup/{file}/download', [BackupController::class, 'download'])->middleware('can:settings.manage')->name('backup.download');
        Route::post('/backup/{file}/restore', [BackupController::class, 'restore'])->middleware('can:settings.manage')->name('backup.restore');
        Route::delete('/backup/{file}', [BackupController::class, 'destroy'])->middleware('can:settings.manage')->name('backup.destroy');
    });
});