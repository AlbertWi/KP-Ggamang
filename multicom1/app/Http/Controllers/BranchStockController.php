<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchStockController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');

        $branches = Branch::with(['inventoryItems' => function ($q) {
            $q->where('status', 'in_stock')->with('product');
        }])->get();

        return view('kepala_toko.stok_cabang.index', compact('branches', 'query'));
    }
}
