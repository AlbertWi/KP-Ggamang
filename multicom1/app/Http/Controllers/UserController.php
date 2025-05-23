<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Branch;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('owner.users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('owner.users.create', compact('branches'));
    }

    public function store(Request $request)
    {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
        'role' => 'required|in:admin,kepala_toko',
        'branch_id' => 'required|exists:branches,id',
        ]);

    // Cek apakah sudah ada user dengan cabang dan role yang sama
    $exists = \App\Models\User::where('branch_id', $validated['branch_id'])
                ->where('role', $validated['role'])
                ->exists();

    if ($exists) {
        return back()->withErrors(['branch_id' => 'User dengan peran ini sudah terdaftar untuk cabang tersebut.'])->withInput();
        }
    $validated['password'] = bcrypt($validated['password']);
    \App\Models\User::create($validated);
    return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }
    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('owner.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $branches = Branch::all();
        return view('owner.users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'role' => 'sometimes|in:admin,kepala_toko,owner',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
