<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    // Menampilkan semua pembelian
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'branch'])->latest()->get();
        return view('admin.purchases.index', compact('purchases'));
    }

    // Menampilkan form tambah pembelian
    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        return view('admin.purchases.create', compact('suppliers', 'products'));
    }

    // Menyimpan data pembelian baru

    public function store(Request $request)
{
    // Debug user authentication
    if (!Auth::check()) {
        return back()->withErrors(['error' => 'User tidak terautentikasi']);
    }

    $user = Auth::user();
    if (!$user->branch_id) {
        return back()->withErrors(['error' => 'User tidak memiliki branch_id']);
    }

    // Debug supplier existence
    $supplier = \App\Models\Supplier::find($request->supplier_id);
    if (!$supplier) {
        return back()->withErrors(['error' => 'Supplier tidak ditemukan']);
    }

    // Debug products existence
    if (!$request->items || !is_array($request->items)) {
        return back()->withErrors(['error' => 'Items tidak valid']);
    }

    foreach ($request->items as $item) {
        $product = \App\Models\Product::find($item['product_id']);
        if (!$product) {
            return back()->withErrors(['error' => "Product ID {$item['product_id']} tidak ditemukan"]);
        }
    }

    // Validasi request
    $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'purchase_date' => 'required|date',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.qty' => 'required|numeric|min:1',
        'items.*.price' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        // Buat Purchase
        $purchase = Purchase::create([
            'user_id' => Auth::id(),
            'branch_id' => $user->branch_id,
            'supplier_id' => $request->supplier_id,
            'purchase_date' => $request->purchase_date,
        ]);

        // Buat Purchase Items
        foreach ($request->items as $item) {
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
            ]);
        }

        DB::commit();
        return redirect()->route('purchases.index')->with('success', 'Pembelian berhasil disimpan.');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Purchase creation failed: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
}
    public function show(Purchase $purchase)
    {
        $purchase->load('items.product'); // Load relasi
        return view('admin.purchases.show', compact('purchase'));
    }
}
