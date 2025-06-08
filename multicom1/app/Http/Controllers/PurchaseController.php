<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'branch'])->latest()->get();
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        return view('admin.purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $branchId = $user->branch_id ?? Branch::first()?->id;

        DB::beginTransaction();
        try {
            // 1. Simpan data pembelian
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
            ]);

            // 2. Simpan item pembelian dan isi inventory_items
            foreach ($request->items as $item) {
                // Buat purchase item dulu
                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);

                // Pastikan purchase item sudah ter-save dengan ID
                $purchaseItem->refresh();

                // Buat inventory items sebanyak qty dengan purchase_item_id yang benar
                for ($i = 0; $i < $item['qty']; $i++) {
                    InventoryItem::create([
                        'branch_id' => $branchId,
                        'product_id' => $item['product_id'],
                        'imei' => null,
                        'purchase_item_id' => $purchaseItem->id, // Pastikan ini ter-isi
                        'status' => 'in_stock',
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Pembelian berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error untuk debugging
            \Log::error('Purchase store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan pembelian: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with([
            'supplier',
            'branch',
            'items.product',
            'items.inventoryItems.product'
        ])->findOrFail($id);
        return view('admin.purchases.show', compact('purchase'));
    }

    public function saveImei(Request $request, Purchase $purchase)
    {
    $imeis = $request->input('imeis', []);

    foreach ($imeis as $inventoryId => $imei) {
        $inventory = InventoryItem::find($inventoryId);
        if ($inventory && $inventory->purchase_item_id == $purchase->id) {
            $inventory->imei = $imei;
            $inventory->save();
        }
    }
    return redirect()->route('purchases.index')->with('success', 'IMEI berhasil disimpan.');
}
}
