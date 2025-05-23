<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\Request;

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
        $products = Product::all();

        return view('admin.stock_transfers.create', compact('branches', 'products', 'userBranchId'));
    }


    // Simpan data transfer stok baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_branch_id' => 'required|exists:branches,id|different:to_branch_id',
            'to_branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        // Buat transfer
        $transfer = StockTransfer::create([
            'from_branch_id' => $validated['from_branch_id'],
            'to_branch_id' => $validated['to_branch_id'],
        ]);

        // Tambah item transfer
        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
        ]);

        return redirect()->route('stock-transfers.index')->with('success', 'Transfer stok berhasil disimpan.');
    }

    // Tampilkan detail transfer stok
    public function show($id)
    {
        $stockTransfer = StockTransfer::with(['fromBranch', 'toBranch', 'items.product'])->findOrFail($id);
        return view('admin.stock_transfers.show', compact('stockTransfer'));
    }
}
