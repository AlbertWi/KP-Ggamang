<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index()
    {
        $stockTransfers = StockTransfer::with(['fromBranch', 'toBranch'])->latest()->get();
        return view('admin.stock_transfers.index', compact('stockTransfers'));
    }

    public function create()
    {
        $userBranchId = auth()->user()->branch_id;
        $branches = Branch::where('id', '!=', $userBranchId)->get();

        $availableStocks = InventoryItem::select('product_id', DB::raw('COUNT(*) as qty'))
        ->where('branch_id', $userBranchId)
        ->where('status', 'in_stock')
        ->groupBy('product_id')
        ->with('product') // agar bisa akses nama produk
        ->get();
        $products = Product::all();

        return view('admin.stock_transfers.create', compact('branches', 'products', 'userBranchId','availableStocks'));
    }


    // Simpan data transfer stok baru
    
public function store(Request $request)
{
    $request->validate([
        'to_branch_id' => 'required|exists:branches,id',
        'imeis' => 'required|array|min:1',
        'imeis.*' => 'required|string|distinct'
    ]);

    $fromBranchId = auth()->user()->branch_id;
    $toBranchId = $request->to_branch_id;
    $imeis = $request->imeis;

    $inventoryItems = \App\Models\InventoryItem::whereIn('imei', $imeis)
        ->whereHas('inventory', function ($q) use ($fromBranchId) {
            $q->where('branch_id', $fromBranchId);
        })
        ->get();

    if (count($inventoryItems) != count($imeis)) {
        return back()->withErrors(['Beberapa IMEI tidak ditemukan atau tidak tersedia di cabang saat ini.'])->withInput();
    }

    // Simpan data transfer stok dan pindahkan itemnya
    DB::transaction(function () use ($inventoryItems, $toBranchId) {
        $transfer = \App\Models\StockTransfer::create([
            'from_branch_id' => auth()->user()->branch_id,
            'to_branch_id' => $toBranchId,
            'user_id' => auth()->id()
        ]);

        foreach ($inventoryItems as $item) {
            $item->inventory->branch_id = $toBranchId;
            $item->inventory->save();

            $transfer->items()->create([
                'inventory_item_id' => $item->id
            ]);
        }
    });

    return redirect()->route('stock-transfers.index')->with('success', 'Transfer berhasil disimpan.');
}



    // Tampilkan detail transfer stok
    public function show($id)
    {
        $stockTransfer = StockTransfer::with(['fromBranch', 'toBranch', 'items.product'])->findOrFail($id);
        return view('admin.stock_transfers.show', compact('stockTransfer'));
    }
}
