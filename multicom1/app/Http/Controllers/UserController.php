<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('branch')->get();
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
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:6|confirmed',
            'role'      => 'required|in:admin,kepala_toko',
            'branch_id' => 'required|exists:branches,id',
        ],[
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid. Contoh: user@example.com',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role harus dipilih.',
            'role.in' => 'Role yang dipilih tidak valid.',
            'branch_id.required' => 'Cabang toko harus dipilih.',
            'branch_id.exists' => 'Cabang toko yang dipilih tidak valid.',
        ]);

        $existingUser = User::where('branch_id', $validated['branch_id'])->first();
        if ($existingUser) {
            return back()->withInput()->withErrors(['branch_id' => 'Cabang ini sudah memiliki user.']);
        }

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function show($id)
    {
        $user = User::with('branch')->findOrFail($id);
        return view('owner.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user     = User::findOrFail($id);
        $branches = Branch::all();
        return view('owner.users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'required|sometimes|string|max:255',
            'email'     => 'required|sometimes|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|min:6|confirmed',
            'branch_id' => 'nullable|exists:branches,id',
        ],[
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid. Contoh: user@example.com',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'branch_id.exists' => 'Cabang toko yang dipilih tidak valid.',
        ]);

        if (!empty($validated['branch_id'])) {
            // Cek jika cabang sudah dipakai user lain
            $exists = User::where('branch_id', $validated['branch_id'])
                        ->where('id', '!=', $user->id)
                        ->first();
            if ($exists) {
                return back()->withInput()->withErrors(['branch_id' => 'Cabang ini sudah memiliki user lain.']);
            }
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }
}