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
    StockController
};

// === AUTH ROUTES ===
Route::get('/', fn () => redirect()->route('login'));

Route::get('/debug-auth', function () {
    return [
        'authenticated' => auth()->check(),
        'user' => auth()->check() ? [
            'id' => auth()->id(),
            'name' => auth()->user()->name ?? null,
            'email' => auth()->user()->email ?? null,
            'role' => auth()->user()->role ?? null
        ] : null
    ];
});

Route::get('/debug-owner-role', fn () => ['message' => 'You have owner access!'])->middleware(['auth', 'role:owner']);
Route::get('/debug-admin-role', fn () => ['message' => 'You have admin access!'])->middleware(['auth', 'role:admin']);

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
    });
    // === OWNER ===
    Route::middleware('role:owner')->group(function () {
        //Route::get('inventory-items', [InventoryItemController::class, 'index'])->name('inventory.index');
        //Route::get('inventory-items/{branch}', [InventoryItemController::class, 'show'])->name('inventory.show');
        Route::resource('inventory', InventoryItemController::class);
        Route::resource('branches', BranchController::class);
        Route::resource('users', UserController::class);
    });

    // === ADMIN ===
    Route::middleware('role:admin')->group(function () {
        Route::Resource('products', ProductController::class);
        Route::resource('suppliers', SupplierController::class);
        //Route::Resource('purchase-items', PurchaseItemController::class);
        Route::resource('stocks', StockController::class);
        //Route::resource('purchases', PurchaseController::class);
        Route::post('/purchases/{purchase}/save-imei', [PurchaseController::class, 'saveImei'])->name('purchases.save_imei');
        Route::get('/stocks/imei/{product}', [StockController::class, 'showImei'])->name('stocks.imei');
        Route::resource('brands', BrandController::class);
    });

    // === KEPALA TOKO ===
    Route::middleware('role:kepala_toko')->group(function () {
        Route::resource('product', ProductController::class);
        Route::resource('sales', SaleController::class);
    });
});
