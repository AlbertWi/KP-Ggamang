<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Type;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Product::with(['brand', 'type']);
        if ($request->has('q') && $request->q !== '') {
            $keyword = $request->q;
            $query->where('name', 'LIKE', "%{$keyword}%");
        }
        $products = $query->get();
        $brands = \App\Models\Brand::all();
        $types = \App\Models\Type::all();
        return view('admin.products.index', compact('products','brands', 'types'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|string|max:255',
            'type_id' => 'required|exists:types,id'
        ],[
            'name.required' => 'Nama Produk harus diisi.',
            'brand_id.required' => 'Brand harus diisi.',
            'type_id.required' => 'Type harus diisi.',
        ]);
        Product::create($validated);
        return redirect()->route('products.index')
            ->with('success', 'Product Berhasil Ditambah');
    }
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ],[
            'name.required' => 'Nama Produk harus diisi.',
        ]);
        $product->update($validated);
        return redirect()->route('products.index')
            ->with('success', 'Product Berhasil Diperbarui');
    }
}
