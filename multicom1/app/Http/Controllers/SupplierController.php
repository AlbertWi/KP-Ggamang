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

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|digits_between:8,15|numeric',
        'address' => 'required|string',
    ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:100',
        'phone' => 'required|digits_between:8,15|numeric',
        'address' => 'required|string|max:255',
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
