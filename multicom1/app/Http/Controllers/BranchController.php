<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        return view('owner.branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string'
        ],[
            'name.required' => 'Nama cabang harus diisi.',
        ]);
        $latestBranch = Branch::orderBy('id', 'desc')->first();
        $nextId = $latestBranch ? $latestBranch->id + 1 : 1;
        $branchCode = 'BR' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        $validated['code'] = $branchCode;
        Branch::create($validated);
        return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string'
        ],[
            'name.required' => 'Nama cabang harus diisi.',
        ]);
        $validated['code'] = $branch->code;
        $branch->update($validated);
        return redirect()->route('branches.index')->with('success', 'Cabang berhasil diperbarui.');
    }
}
