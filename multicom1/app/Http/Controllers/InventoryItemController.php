<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\Branch;
use App\Models\Product;

class InventoryItemController extends Controller
{
    // Menampilkan stok per cabang
    public function index()
    {
        $branches = Branch::all();
        return view('admin.inventory.index', compact('branches'));
    }

    // Menampilkan stok untuk cabang tertentu
    public function show($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $inventory = InventoryItem::with('product')
            ->where('branch_id', $branchId)
            ->get();

        return view('admin.inventory.show', compact('branch', 'inventory'));
    }
}
