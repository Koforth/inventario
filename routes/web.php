<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PresentationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/home');

    Route::get('/home', function () {
        return view('home');
    })->name('home');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index')->can('view-catalog');

    Route::middleware('can:create-products')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    });

    Route::middleware('can:manage-products')->group(function () {
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

        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    Route::middleware('can:view-reports')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->can('view-reports');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export')->can('view-reports');
    });

    Route::middleware('can:manage-users')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    });

    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show')->can('view-catalog');

    Route::middleware('can:manage-inventory')->group(function () {
        Route::get('/inventory/entries', [InventoryController::class, 'entries'])->name('inventory.entries.index');
        Route::post('/inventory/entries', [InventoryController::class, 'store'])->name('inventory.entries.store');
        Route::get('/inventory/sales', [InventoryController::class, 'sales'])->name('inventory.sales.index');
        Route::post('/inventory/sales', [InventoryController::class, 'processSale'])->name('inventory.sales.process');
    });

    Route::middleware('can:manage-quotes')->group(function () {
        Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
        Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
        Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    });

    Route::middleware('can:manage-purchases')->group(function () {
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

    Route::middleware('can:manage-cash')->group(function () {
        Route::get('/cash', [CashRegisterController::class, 'index'])->name('cash.index');
        Route::post('/cash/open', [CashRegisterController::class, 'open'])->name('cash.open');
        Route::put('/cash/{cashRegister}/close', [CashRegisterController::class, 'close'])->name('cash.close');
        Route::post('/cash/{cashRegister}/movements', [CashRegisterController::class, 'movement'])->name('cash.movements.store');
    });
});
