<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class StockController extends Controller
{
    public function index()
    {
    $branchId = auth()->user()->branch_id;

    $stocks = InventoryItem::select('product_id', DB::raw('count(*) as qty'))
        ->where('branch_id', $branchId)
        ->where('status', 'in_stock') // asumsi status "in" artinya masih ada di cabang
        ->groupBy('product_id')
        ->with('product') // pastikan relasi ke Product
        ->get();

    return view('admin.stocks.index', compact('stocks'));
    }
    public function showImei($productId)
    {
    $inventoryItems = \App\Models\InventoryItem::where('product_id', $productId)
        ->whereNotNull('imei')
        ->get();

        $product = \App\Models\Product::findOrFail($productId);

    return view('admin.stocks.imei', compact('inventoryItems', 'product'));
    }
}
