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
    $validated = $request->validate([
        'from_branch_id' => 'required|exists:branches,id|different:to_branch_id',
        'to_branch_id' => 'required|exists:branches,id',
        'inventory_item_id' => 'required|exists:inventory_items,id',
        'quantity' => 'required|integer|min:1'
    ]);

    // Ambil data inventory item
    $inventoryItem = InventoryItem::with('product')->findOrFail($validated['inventory_item_id']);

    // Validasi: pastikan inventory sesuai cabang asal
    if ($inventoryItem->branch_id != $validated['from_branch_id']) {
        return back()->withErrors('Produk tidak tersedia di cabang asal yang dipilih.');
    }

    // Validasi: cukup stok
    if ($validated['quantity'] > $inventoryItem->quantity) {
        return back()->withErrors('Jumlah stok tidak mencukupi.');
    }

    // Buat transfer
    $transfer = StockTransfer::create([
        'from_branch_id' => $validated['from_branch_id'],
        'to_branch_id' => $validated['to_branch_id'],
    ]);

    // Buat item transfer
    StockTransferItem::create([
        'stock_transfer_id' => $transfer->id,
        'product_id' => $inventoryItem->product_id,
        'quantity' => $validated['quantity'],
    ]);

    // Kurangi stok dari cabang asal
    $inventoryItem->decrement('quantity', $validated['quantity']);

    // Tambahkan stok ke cabang tujuan (jika tidak ada, buat baru)
    $targetInventory = InventoryItem::firstOrCreate(
        [
            'branch_id' => $validated['to_branch_id'],
            'product_id' => $inventoryItem->product_id
        ],
        ['quantity' => 0]
    );
    $targetInventory->increment('quantity', $validated['quantity']);

    return redirect()->route('stock-transfers.index')->with('success', 'Transfer stok berhasil disimpan.');
}


    // Tampilkan detail transfer stok
    public function show($id)
    {
        $stockTransfer = StockTransfer::with(['fromBranch', 'toBranch', 'items.product'])->findOrFail($id);
        return view('admin.stock_transfers.show', compact('stockTransfer'));
    }
}
