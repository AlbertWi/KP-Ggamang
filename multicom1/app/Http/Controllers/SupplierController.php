<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('admin.suppliers.index', compact('suppliers'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|digits_between:8,15|numeric',
        'address' => 'required|string',
    ],[
        'name.required' => 'Nama Supplier harus diisi.',
        'phone.required' => 'No.Telepon harus diisi.',
        'address.required' => 'Alamat harus diisi.',
        'phone.numeric' => 'No.telepon harus diisi dengan Angka.',
        'phone.digits_between' => 'No.telepon harus diisi dengan 8-15 Angka.',
    ]);
        Supplier::create($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|digits_between:8,15|numeric',
            'address' => 'required|string|max:255',
        ],[
            'name.required' => 'Nama Supplier harus diisi.',
            'phone.required' => 'No.Telepon harus diisi.',
            'address.required' => 'Alamat harus diisi.',
        ]);
        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }
}
