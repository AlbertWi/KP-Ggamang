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

    public function create()
    {
        return view('owner.branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string'
        ]);

        // Generate automatic code with "BR" prefix and increment
        $latestBranch = Branch::orderBy('id', 'desc')->first();
        $nextId = $latestBranch ? $latestBranch->id + 1 : 1;
        $branchCode = 'BR' . str_pad($nextId, 3, '0', STR_PAD_LEFT); // Format: BR001, BR002, etc.

        // Add code to validated data
        $validated['code'] = $branchCode;

        Branch::create($validated);

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $branch = Branch::findOrFail($id);
        return view('owner.branches.show', compact('branch'));
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        return view('owner.branches.edit', compact('branch'));
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string'
        ]);

        $validated['code'] = $branch->code;

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil dihapus.');
    }
}
