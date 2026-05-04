<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LayawayController;
use App\Http\Controllers\KardexController;
use App\Http\Controllers\PresentationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReceiptTypeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/home');

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index')->can('catalog.view');

    Route::middleware('can:products.create')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    });

    Route::middleware('can:warehouse.manage')->group(function () {
        Route::get('/warehouse', [WarehouseController::class, 'index'])->name('warehouse.index');
        Route::get('/warehouse/perishables', [WarehouseController::class, 'perishables'])->name('warehouse.perishables');
        Route::resource('/warehouse/categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names('warehouse.categories');
        Route::resource('/warehouse/brands', BrandController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names('warehouse.brands');
        Route::resource('/warehouse/presentations', PresentationController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names('warehouse.presentations');

    });

    Route::middleware('can:products.manage')->group(function () {
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    Route::middleware('can:reports.manage')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware('can:users.manage')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    });

    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show')->can('catalog.view');

    Route::middleware('can:inventory.entries.manage')->group(function () {
        Route::get('/inventory/entries', [InventoryController::class, 'entries'])->name('inventory.entries.index');
        Route::post('/inventory/entries', [InventoryController::class, 'store'])->name('inventory.entries.store');
    });

    Route::middleware('can:customers.manage')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    });

    Route::middleware('can:sales.manage')->group(function () {
        Route::get('/inventory/sales', [InventoryController::class, 'sales'])->name('inventory.sales.index');
        Route::post('/inventory/sales', [InventoryController::class, 'processSale'])->name('inventory.sales.process');
    });

    Route::middleware('can:layaways.manage')->group(function () {
        Route::get('/inventory/layaways', [LayawayController::class, 'index'])->name('inventory.layaways.index');
        Route::post('/inventory/layaways', [LayawayController::class, 'store'])->name('inventory.layaways.store');
        Route::post('/inventory/layaways/{layaway}/payments', [LayawayController::class, 'pay'])->name('inventory.layaways.pay');
        Route::post('/inventory/layaways/{layaway}/complete', [LayawayController::class, 'complete'])->name('inventory.layaways.complete');
    });

    Route::middleware('can:sales.manage')->group(function () {
        Route::post('/inventory/credit-sales/{sale}/payments', [LayawayController::class, 'payCredit'])->name('inventory.credit-sales.pay');
    });

    Route::middleware('can:kardex.manage')->group(function () {
        Route::get('/inventory/kardex', [KardexController::class, 'index'])->name('inventory.kardex.index');
        Route::post('/inventory/kardex/move', [KardexController::class, 'move'])->name('inventory.kardex.move');
    });

    Route::middleware('can:technicians.manage')->group(function () {
        Route::get('/inventory/technicians', [TechnicianController::class, 'index'])->name('inventory.technicians.index');
        Route::post('/inventory/technicians', [TechnicianController::class, 'store'])->name('inventory.technicians.store');
    });

    Route::middleware('can:workshop.manage')->group(function () {
        Route::get('/inventory/workshop', [WorkshopController::class, 'index'])->name('inventory.workshop.index');
        Route::post('/inventory/workshop', [WorkshopController::class, 'store'])->name('inventory.workshop.store');
    });

    Route::middleware('can:receipts.manage')->group(function () {
        Route::get('/inventory/receipts', [ReceiptTypeController::class, 'index'])->name('inventory.receipts.index');
        Route::post('/inventory/receipts', [ReceiptTypeController::class, 'store'])->name('inventory.receipts.store');
        Route::put('/inventory/receipts/{receiptType}', [ReceiptTypeController::class, 'update'])->name('inventory.receipts.update');
    });

    Route::middleware('can:quotes.manage')->group(function () {
        Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
        Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
        Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    });

    Route::middleware('can:purchases.manage')->group(function () {
        Route::resource('/suppliers', SupplierController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names('suppliers');
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/credits', [PurchaseController::class, 'credits'])->name('purchases.credits');
        Route::post('/purchases/{purchase}/payments', [PurchaseController::class, 'payment'])->name('purchases.payments.store');
        Route::get('/purchases/price-history', [PurchaseController::class, 'priceHistory'])->name('purchases.price-history');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });

    Route::middleware('can:cash.manage')->group(function () {
        Route::get('/cash', [CashRegisterController::class, 'index'])->name('cash.index');
        Route::post('/cash/open', [CashRegisterController::class, 'open'])->name('cash.open');
        Route::put('/cash/{cashRegister}/close', [CashRegisterController::class, 'close'])->name('cash.close');
        Route::post('/cash/{cashRegister}/movements', [CashRegisterController::class, 'movement'])->name('cash.movements.store');
    });
});
