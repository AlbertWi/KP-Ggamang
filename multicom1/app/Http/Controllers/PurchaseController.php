<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\Inventory;
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
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
            ]);

            foreach ($request->items as $item) {
                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);

                $purchaseItem->refresh();

                // Buat inventory master
                $inventory = Inventory::firstOrCreate([
                    'product_id' => $item['product_id'],
                    'branch_id' => $branchId,
                ]);

                // Buat inventory items
                for ($i = 0; $i < $item['qty']; $i++) {
                    InventoryItem::create([
                        'branch_id' => $branchId,
                        'product_id' => $item['product_id'],
                        'inventory_id' => $inventory->id,
                        'imei' => null,
                        'purchase_item_id' => $purchaseItem->id,
                        'status' => 'in_stock',
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Pembelian berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
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

        $allInventories = $purchase->items->flatMap(function ($item) {
            return $item->inventoryItems;
        });

        foreach ($allInventories as $inventory) {
            $inputImei = $imeis[$inventory->id] ?? null;
            if (!$inputImei) continue;

            $product = $inventory->product;
            $type_id = $product->type_id;

            // Validasi IMEI tidak boleh ganda di produk dengan tipe sama
            $duplicate = InventoryItem::where('imei', $inputImei)
                ->whereHas('product', function ($query) use ($type_id) {
                    $query->where('type_id', $type_id);
                })
                ->where('id', '!=', $inventory->id)
                ->exists();

            if ($duplicate) {
                return back()->withErrors([
                    'IMEI ' . $inputImei . ' sudah digunakan pada produk dengan tipe yang sama.'
                ])->withInput();
            }

            // Tambah atau ambil inventory master
            $inv = Inventory::firstOrCreate([
                'product_id' => $product->id,
                'branch_id' => $inventory->branch_id,
            ]);

            $inventory->imei = $inputImei;
            $inventory->inventory_id = $inv->id;
            $inventory->save();
        }

        return redirect()->route('purchases.index')->with('success', 'IMEI berhasil disimpan.');
    }
}
