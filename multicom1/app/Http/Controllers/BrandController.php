<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::all();
        return view('admin.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:brands,name',
        ],[
            'name.required' => 'Nama Brand harus diisi.',
        ]);
        Brand::create([
            'name' => $request->name,
        ]);
        return redirect()->route('brands.index')->with('success', 'Merek berhasil ditambahkan.');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ],[
            'name.required' => 'nama Brand harus diisi.',
        ]);

        $brand = \App\Models\Brand::findOrFail($id);
        $brand->update([
            'name' => $request->name
        ]);

        return redirect()->route('brands.index')->with('success', 'Brand berhasil diperbarui.');
    }

}
