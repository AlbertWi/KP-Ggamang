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
    AuthController
};
Route::get('/', function () {
    return redirect()->route('login');
});
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

// Test role middleware directly
Route::get('/debug-owner-role', function () {
    return ['message' => 'You have owner access!'];
})->middleware(['auth', 'role:owner']);

// Test other roles for comparison
Route::get('/debug-admin-role', function () {
    return ['message' => 'You have admin access!'];
})->middleware(['auth', 'role:admin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    Route::middleware('role:owner')->group(function () {
        Route::get('inventory-items', [InventoryItemController::class, 'index'])->name('inventory.index');
        Route::get('inventory-items/{branch}', [InventoryItemController::class, 'show'])->name('inventory.show');
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('branches/{branch}', [BranchController::class, 'show'])->name('branches.show');
        Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('role:kepala_toko')->group(function () {
        Route::apiResource('sales', SaleController::class)->only(['index', 'show', 'store']);
        Route::apiResource('sale-items', SaleItemController::class)->only(['index', 'show']);
        Route::get('suppliers', [SupplierController::class, 'index']);
        Route::get('suppliers/{id}', [SupplierController::class, 'show']);
        Route::get('products', [ProductController::class, 'index']);
        //Route::get('inventory-items', [InventoryItemController::class, 'index'])->name('inventory-items.index');
        Route::apiResource('stock-transfers', StockTransferController::class)->only(['index', 'show', 'store']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('products', ProductController::class)->only(['index', 'show', 'store','create']);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('purchases', PurchaseController::class)->only(['index', 'show', 'store']);
        Route::apiResource('purchase-items', PurchaseItemController::class)->only(['index', 'show']);
        Route::apiResource('stock-transfers', StockTransferController::class)->only(['index', 'show', 'store']);
    });
});
