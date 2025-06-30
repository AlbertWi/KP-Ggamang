<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function index()
    {
        $types = Type::all();
        return view('admin.types.index', compact('types'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:types,name',
        ],[
            'name.required' => 'Nama Type harus diisi.',
        ]);
        Type::create(['name' => $request->name]);
        return redirect()->route('types.index')->with('success', 'Tipe berhasil ditambahkan.');
    }
    public function update(Request $request, Type $type)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:types,name,' . $type->id,
        ],[
            'name.required' => 'Nama Type harus diisi.',
        ]);
        $type->update(['name' => $request->name]);
        return redirect()->route('types.index')->with('success', 'Tipe berhasil diperbarui.');
    }
}
