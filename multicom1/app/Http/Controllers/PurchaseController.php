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
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function index()
{
    $user = auth()->user();
    if ($user->role === 'kepala_toko') {
        $purchases = Purchase::where('branch_id', $user->branch_id)->latest()->get();
    } else {
        $purchases = Purchase::latest()->get();
    }
    return view('kepala_toko.purchases.index', compact('purchases'));
}
    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        return view('admin.purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $normalizedItems = [];
        foreach ($request->items as $i => $item) {
            $normalizedItems[$i] = $item;
            $normalizedItems[$i]['price'] = str_replace(',', '', $item['price'] ?? 0);
            $normalizedItems[$i]['qty'] = str_replace(',', '', $item['qty'] ?? 0);
        }
        $request->merge([
            'items' => $normalizedItems
        ]);
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ],[
            'supplier_id.required' => 'Supplier harus dipilih.',
            'items.*.product_id.required' => 'Product Harus Diisi.',
            'items.*.qty.required' => 'Qty Harus Diisi',
            'items.*.qty.numeric' => 'Qty Harus Diisi dengan angka',
            'items.*.qty.min' => 'Qty Harus Diisi dengan Minimal 1',
            'items.*.price.required' => 'Harga Harus Diisi',
            'items.*.price.numeric' => 'Harga Harus Diisi dengan angka',
            'items.*.price.min' => 'Harga Harus Diisi dengan Minimal Rp 1',
        ]);
        $purchaseDate = Carbon::now();
        $user = Auth::user();
        $branchId = $user->branch_id ?? Branch::first()?->id;

        DB::beginTransaction();
        try {
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $purchaseDate,
            ]);

            foreach ($request->items as $item) {
                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);

                $purchaseItem->refresh();

                $inventory = Inventory::firstOrCreate([
                    'product_id' => $item['product_id'],
                    'branch_id' => $branchId,
                ]);
                $inventory->qty = ($inventory->exists ? $inventory->qty : 0) + $item['qty'];
                $inventory->save();

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
            return redirect()->route('purchases.index')->with('success', 'Pembelian berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan pembelian: ' . $e->getMessage());
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
        $inventories = $purchase->items->flatMap(fn ($item) => $item->inventoryItems);
        $errorMessages = [];

        foreach ($inventories as $inventory) {
            $inputImei = trim($imeis[$inventory->id] ?? '');

            if (!$inputImei) continue;

            $product = $inventory->product;
            $typeId = $product->type_id;

            $isDuplicate = InventoryItem::where('imei', $inputImei)
                ->where('id', '!=', $inventory->id)
                ->exists();

            if ($isDuplicate) {
                $errorMessages[] = "IMEI $inputImei sudah digunakan.";
                continue;
            }

            try {
                $inventory->imei = $inputImei;
                $inventory->save();
            } catch (\Exception $e) {
                \Log::error('Gagal simpan IMEI: ' . $e->getMessage());
                $errorMessages[] = "Gagal menyimpan IMEI <strong>{$inputImei}</strong>: {$e->getMessage()}";
            }
        }

        if (count($errorMessages) > 0) {
            return redirect()->back()->withErrors($errorMessages)->withInput();
        }

        return redirect()->route('purchases.index')->with('success', 'IMEI berhasil disimpan.');
    }

}
