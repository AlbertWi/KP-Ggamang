<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return redirect()->route('dashboard');
        }

        return response()->json([
            'message' => 'Login failed'
        ], 401);
    }

    public function logout(Request $request)
{
    $accessToken = $request->user()->currentAccessToken();

    // Check if the token can be deleted
    if (method_exists($accessToken, 'delete')) {
        $accessToken->delete();
    }

    // Force logout
    Auth::guard('web')->logout();

    return redirect()->route('login');
}
    public function showLoginForm()
{
    return view('auth.login');
}
public function dashboard()
{
    $user = Auth::user();

    // Redirect to role-specific dashboard
    switch ($user->role) {
        case 'admin':
            return view('dashboard.admin',[
                'totalProducts' => \App\Models\Product::count(),
                'totalSuppliers' => \App\Models\Supplier::count(),
                'totalPurchases' => \App\Models\Purchase::count(),
                'totalTransfers' => \App\Models\StockTransfer::count(),
            ]);
        case 'kepala_toko':
            return view('dashboard.kepala_toko');
        case 'owner':
            return view('dashboard.owner');
        default:
            return redirect()->route('login');
    }
}
}
