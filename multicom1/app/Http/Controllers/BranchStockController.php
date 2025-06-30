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
    public function exportStok()
    {
        $branches = \App\Models\Branch::with(['inventoryItems.product'])->get();

        $rows = [];

        foreach ($branches as $branch) {
            $grouped = $branch->inventoryItems->groupBy('product_id');

            foreach ($grouped as $productId => $items) {
                $product = $items->first()->product;
                $rows[] = [
                    'Cabang' => $branch->name,
                    'Produk' => $product->name,
                    'Qty' => $items->count(),
                    'Brand' => $product->brand->name ?? '-',
                ];
            }
        }

        $filename = 'rptstock' . '.csv';

        // Buat CSV
        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);

        $csv = stream_get_contents($handle);
        fclose($handle);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
