<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use App\Models\Product;

class SaleController extends Controller
{
    public function index()
    {
        $sales = \App\Models\Sale::with('items')->get();
        return view('kepala_toko.sales.index', compact('sales'));
    }

    public function show($id)
    {
        $sale = \App\Models\Sale::with('items')->findOrFail($id);
        return view('kepala_toko.sales.show', compact('sale'));
    }
    public function create()
    {
    $products = Product::all();
    return view('kepala_toko.sales.create', compact('products'));
    }

    public function store(Request $request)
    {
    $validated = $request->validate([
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.imei' => 'required|string|max:255',
        'items.*.price' => 'required|numeric|min:0',
    ]);

    $sale = \App\Models\Sale::create([]);

    foreach ($request->items as $item) {
        $sale->items()->create($item);
    }

    return redirect()->route('sales.index')->with('success', 'Penjualan berhasil ditambahkan.');
    }

}
