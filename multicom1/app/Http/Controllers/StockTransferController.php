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
    $user = auth()->user();

    if ($user->role === 'kepala_toko') {
        $stockTransfers = StockTransfer::where('from_branch_id', $user->branch_id)
                    ->orWhere('to_branch_id', $user->branch_id)
                    ->latest()->get();
    } else {
        $stockTransfers = StockTransfer::latest()->get();
    }

    return view('kepala_toko.stock_transfers.index', compact('stockTransfers'));
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
        ],[
            'imeis.required' => 'IMEI harus diisi.',
            'imeis.*.required' => 'IMEI harus diisi.',
        ]);
    
        $fromBranchId = auth()->user()->branch_id;
        $toBranchId = $request->to_branch_id;
        $imeis = $request->imeis;
    
        // Perbaikan: Query langsung ke inventory_items berdasarkan branch_id
        $inventoryItems = \App\Models\InventoryItem::whereIn('imei', $imeis)
            ->where('branch_id', $fromBranchId)
            ->where('status', 'in_stock')
            ->get();
    
        // Validasi: Pastikan semua IMEI ditemukan
        if (count($inventoryItems) != count($imeis)) {
            $foundImeis = $inventoryItems->pluck('imei')->toArray();
            $missingImeis = array_diff($imeis, $foundImeis);
            
            return back()->withErrors([
                'imeis' => 'IMEI tidak ditemukan atau tidak tersedia: ' . implode(', ', $missingImeis)
            ])->withInput();
        }
    
        try {
            DB::transaction(function () use ($inventoryItems, $toBranchId, $fromBranchId) {
                // 1. Buat record stock transfer
                $transfer = StockTransfer::create([
                    'from_branch_id' => $fromBranchId,
                    'to_branch_id' => $toBranchId,
                    'user_id' => auth()->id()
                ]);
    
                // 2. Proses setiap item
                foreach ($inventoryItems as $item) {
                    // Simpan item transfer
                    $transfer->items()->create([
                        'inventory_item_id' => $item->id
                    ]);
    
                    // Update branch_id dan status inventory_item
                    $item->update([
                        'branch_id' => $toBranchId,
                        'status' => 'in_stock' // Pastikan status tetap in_stock
                    ]);
    
                    // Update inventory di cabang asal (kurangi)
                    $fromInventory = \App\Models\Inventory::where([
                        'branch_id' => $fromBranchId,
                        'product_id' => $item->product_id,
                    ])->first();
    
                    if ($fromInventory) {
                        $fromInventory->update([
                            'qty' => max(0, $fromInventory->qty - 1)
                        ]);
                    }
    
                    // Update inventory di cabang tujuan (tambah)
                    $toInventory = \App\Models\Inventory::where([
                        'branch_id' => $toBranchId,
                        'product_id' => $item->product_id,
                    ])->first();
    
                    if ($toInventory) {
                        $toInventory->increment('qty');
                    } else {
                        \App\Models\Inventory::create([
                            'branch_id' => $toBranchId,
                            'product_id' => $item->product_id,
                            'qty' => 1
                        ]);
                    }
                }
            });
    
            return redirect()->route('stock-transfers.index')
                ->with('success', 'Transfer berhasil disimpan.');
    
        } catch (\Exception $e) {
            \Log::error('Stock Transfer Error: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat menyimpan transfer. Silakan coba lagi.'
            ])->withInput();
        }
    }
    public function show($id)
    {
        $stockTransfer = \App\Models\StockTransfer::with(['fromBranch', 'toBranch', 'items.inventoryItem.product'])
                        ->findOrFail($id);
    
        $view = match (auth()->user()->role) {
            'admin' => 'admin.stock_transfers.show',
            'kepala_toko' => 'kepala_toko.stock_transfers.show',
            default => abort(403, 'Unauthorized'),
        };
    
        return view($view, compact('stockTransfer'));
    }
}
