<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

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
        $branches = Branch::all();
        return view('admin.purchases.create', compact('suppliers', 'products', 'branches'));
    }

    // Menyimpan data pembelian baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        $purchase = Purchase::create([
            'supplier_id' => $validated['supplier_id'],
            'branch_id' => $validated['branch_id'],
            'user_id' => Auth::id(), // Admin yang melakukan input
        ]);

        foreach ($validated['products'] as $item) {
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        return redirect()->route('purchases.index')->with('success', 'Pembelian berhasil disimpan.');
    }

    // Menampilkan detail pembelian
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'branch', 'items.product'])->findOrFail($id);
        return view('admin.purchases.show', compact('purchase'));
    }
}
