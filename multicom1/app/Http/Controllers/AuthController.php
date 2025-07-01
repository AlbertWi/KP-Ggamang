<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\InventoryItem;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ],[
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid. Contoh: user@example.com',
            'password.required' => 'Password Harus diisi',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.'
        ])->withInput();
    }


    public function logout(Request $request)
{
    $accessToken = $request->user()->currentAccessToken();
    if (method_exists($accessToken, 'delete')) {
        $accessToken->delete();
    }
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
    switch ($user->role) {
        case 'admin':
            return view('dashboard.admin', [
                'totalProducts' => \App\Models\Product::count(),
                'totalSuppliers' => \App\Models\Supplier::count(),
                'totalPurchases' => \App\Models\Purchase::count(),
                'totalTransfers' => \App\Models\StockTransfer::count(),
                'totalStockRequests' => \App\Models\StockRequest::count(),
                'pendingStockRequests' => \App\Models\StockRequest::where('status', 'pending')->count(),
            ]);

        case 'kepala_toko':
            $branchId = $user->branch_id;
            $pendingRequestsCount = \App\Models\StockRequest::where('to_branch_id', $branchId)
                                                            ->where('status', 'pending')
                                                            ->count();

            $pendingRequests = \App\Models\StockRequest::where('to_branch_id', $branchId)
                                                        ->where('status', 'pending')
                                                        ->with(['fromBranch', 'product'])
                                                        ->orderBy('created_at', 'desc')
                                                        ->limit(5)
                                                        ->get();

            return view('dashboard.kepala_toko', [
                'productCount' => \App\Models\Product::count(),
                'purchaseCount' => \App\Models\Purchase::count(),
                'supplierCount' => \App\Models\Supplier::count(),
                'transferCount' => \App\Models\StockTransfer::count(),
                'totalPurchases' => \App\Models\Purchase::where('branch_id', $branchId)->count(),
                'totalTransfersIn' => \App\Models\StockTransfer::where('to_branch_id', $branchId)->count(),
                'totalTransfersOut' => \App\Models\StockTransfer::where('from_branch_id', $branchId)->count(),
                'pendingRequestsCount' => $pendingRequestsCount,
                'pendingRequests' => $pendingRequests,
                'totalStockRequestsIn' => \App\Models\StockRequest::where('to_branch_id', $branchId)->count(),
                'totalStockRequestsOut' => \App\Models\StockRequest::where('from_branch_id', $branchId)->count(),
            ]);

            case 'owner':
                $lowStocks = \App\Models\Branch::with(['inventoryItems' => function ($q) {
                    $q->where('status', 'in_stock')->with('product');
                }])->get()->map(function ($branch) {
                    return [
                        'branch' => $branch->name,
                        'low_stocks' => $branch->inventoryItems
                            ->groupBy('product_id')
                            ->map(function ($items) {
                                return [
                                    'product' => $items->first()->product->name ?? 'Tidak diketahui',
                                    'qty' => $items->count()
                                ];
                            })->filter(fn ($item) => $item['qty'] < 2)->values()
                    ];
                })->filter(fn ($branch) => $branch['low_stocks']->isNotEmpty());

                return view('dashboard.owner', [
                    'totalStock' => \App\Models\InventoryItem::where('status', 'in_stock')->count(),
                    'totalBranches' => \App\Models\Branch::count(),
                    'totalAdmins' => \App\Models\User::whereIn('role', ['admin', 'kepala_toko'])->count(),
                    'totalStockRequests' => \App\Models\StockRequest::count(),
                    'pendingStockRequests' => \App\Models\StockRequest::where('status', 'pending')->count(),
                    'lowStocks' => $lowStocks,
                ]);

        default:
            return redirect()->route('login');
    }
}
}
