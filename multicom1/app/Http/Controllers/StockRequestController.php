<?php

namespace App\Http\Controllers;

use App\Models\StockRequest;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Auth;

class StockRequestController extends Controller
{
    public function index() {
        $branchId = Auth::user()->branch_id;
        
        // Ambil request yang melibatkan cabang user (sebagai pengirim atau penerima)
        $requests = StockRequest::where('from_branch_id', $branchId)
                    ->orWhere('to_branch_id', $branchId)
                    ->with(['fromBranch', 'toBranch', 'product'])
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('kepala_toko.stock_requests.index', compact('requests'));
    }

    public function create() {
        $products = Product::all();
        // Hanya tampilkan cabang lain (bukan cabang user sendiri)
        $branches = Branch::where('id', '!=', Auth::user()->branch_id)->get();
        return view('kepala_toko.stock_requests.create', compact('products', 'branches'));
    }

    public function store(Request $request) {
        $request->validate([
            'to_branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
        ]);

        // Pastikan user tidak mengirim request ke cabang sendiri
        if ($request->to_branch_id == Auth::user()->branch_id) {
            return redirect()->back()->withErrors(['to_branch_id' => 'Tidak bisa mengirim request ke cabang sendiri']);
        }

        StockRequest::create([
            'from_branch_id' => Auth::user()->branch_id,
            'to_branch_id' => $request->to_branch_id,
            'product_id' => $request->product_id,
            'qty' => $request->qty,
            'status' => 'pending',
        ]);

        return redirect()->route('stock-requests.index')->with('success', 'Permintaan barang berhasil dibuat.');
    }

    public function approve($id) {
        $stockRequest = StockRequest::findOrFail($id);
        
        // Pastikan hanya cabang tujuan yang bisa approve
        if ($stockRequest->to_branch_id != Auth::user()->branch_id) {
            return redirect()->back()->withErrors(['error' => 'Anda tidak memiliki akses untuk menyetujui request ini']);
        }

        $stockRequest->update(['status' => 'accepted']);
        return redirect()->back()->with('success', 'Permintaan disetujui.');
    }

    public function reject(Request $request, $id) {
        $request->validate(['reason' => 'required']);
        
        $stockRequest = StockRequest::findOrFail($id);
        
        // Pastikan hanya cabang tujuan yang bisa reject
        if ($stockRequest->to_branch_id != Auth::user()->branch_id) {
            return redirect()->back()->withErrors(['error' => 'Anda tidak memiliki akses untuk menolak request ini']);
        }

        $stockRequest->update([
            'status' => 'rejected',
            'reason' => $request->reason
        ]);
        return redirect()->back()->with('success', 'Permintaan ditolak.');
    }

    // Method untuk dashboard - hitung pending requests
    public function getPendingRequestsCount() {
        return StockRequest::where('to_branch_id', Auth::user()->branch_id)
                          ->where('status', 'pending')
                          ->count();
    }

    // Method untuk dashboard - ambil pending requests
    public function getPendingRequests() {
        return StockRequest::where('to_branch_id', Auth::user()->branch_id)
                            ->where('status', 'pending')
                            ->with(['fromBranch', 'product'])
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
    }
}