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

    public function create()
    {
        return view('admin.types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:types,name',
        ]);

        Type::create(['name' => $request->name]);

        return redirect()->route('types.index')->with('success', 'Tipe berhasil ditambahkan.');
    }

    public function edit(Type $type)
    {
        return view('admin.types.edit', compact('type'));
    }

    public function update(Request $request, Type $type)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:types,name,' . $type->id,
        ]);

        $type->update(['name' => $request->name]);

        return redirect()->route('types.index')->with('success', 'Tipe berhasil diperbarui.');
    }
}
