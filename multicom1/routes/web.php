<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    BranchController,
    UserController,
    SupplierController,
    ProductController,
    InventoryItemController,
    PurchaseController,
    PurchaseItemController,
    SaleController,
    SaleItemController,
    StockTransferController,
    StockTransferItemController,
    AuthController,
    BrandController,
    StockController,
    TypeController,
    StockRequestController,
    BranchStockController
};

// === AUTH ROUTES ===
Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');

// === AUTHENTICATED ROUTES ===
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    //shared route
    Route::middleware(['auth', 'role:admin,kepala_toko'])->group(function () {
        Route::resource('purchases', PurchaseController::class);
        Route::resource('purchase-items', PurchaseItemController::class);
        Route::resource('stock-transfers', StockTransferController::class);
        Route::post('/purchases/{purchase}/save-imei', [PurchaseController::class, 'saveImei'])->name('purchases.save_imei');
    });

    Route::middleware(['auth', 'role:owner,kepala_toko,admin'])->group(function () {
        Route::get('/stok-cabang', [\App\Http\Controllers\BranchStockController::class, 'index'])->name('stok-cabang');
        Route::get('/stok/export', [\App\Http\Controllers\BranchStockController::class, 'exportStok'])->name('export-stok');
    });
    // === OWNER ===
    Route::middleware('role:owner')->group(function () {
        Route::resource('inventory', InventoryItemController::class);
        Route::resource('branches', BranchController::class);
        Route::resource('users', UserController::class);
        Route::get('/laporan-penjualan', [\App\Http\Controllers\SaleController::class, 'laporanPenjualan'])->name('owner.laporan.penjualan');
    });

    // === ADMIN ===
    Route::middleware('role:admin')->group(function () {
        Route::Resource('products', ProductController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('stocks', StockController::class);
        Route::get('/stocks/imei/{product}', [StockController::class, 'showImei'])->name('stocks.imei');
        Route::resource('brands', BrandController::class);
        Route::resource('types', TypeController::class);
    });

    // === KEPALA TOKO ===
    Route::middleware('role:kepala_toko')->group(function () {
        Route::resource('product', ProductController::class);
        Route::get('cari-produk-by-imei', [SaleController::class, 'cariByImei'])->name('cari-produk-by-imei');
        Route::get('sales/{id}/input-imei', [SaleController::class, 'inputImei'])->name('sales.input-imei');
        Route::post('sales/{id}/save-imei', [SaleController::class, 'saveImei'])->name('sales.save-imei');
        Route::resource('sales', SaleController::class);
        Route::get('/search-by-imei', [SaleController::class, 'searchByImei'])->name('search.by.imei');
        Route::resource('stock-requests', StockRequestController::class);
        Route::post('stock-requests/{id}/approve', [StockRequestController::class, 'approve'])
            ->name('stock-requests.approve');
        Route::post('stock-requests/{id}/reject', [StockRequestController::class, 'reject'])
            ->name('stock-requests.reject');

    });
});
