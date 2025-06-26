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

        // Jika ada pencarian
        if ($request->has('q') && $request->q !== '') {
            $keyword = $request->q;
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        $products = $query->get(); // atau paginate() kalau kamu pakai pagination

        return view('admin.products.index', compact('products'));
    }



    public function create()
    {
        $brands = Brand::all();
        $types = Type::all();
        return view('admin.products.create', compact('brands','types'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|string|max:255',
            'type_id' => 'required|exists:types,id'
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $brands = Brand::all();
        $types = Type::all();
        return view('admin.products.edit', compact('product'),compact('brands','types'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully');
    }
}
