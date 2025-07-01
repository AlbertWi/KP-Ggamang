<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class BranchStockController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');

        $branches = Branch::with(['inventoryItems' => function ($q) {
            $q->where('status', 'in_stock')->with('product');
        }])->get();
        $selectedBranchId = $request->branch_id;
        return view('stok_cabang.index', compact('branches', 'query'));
    }
}
