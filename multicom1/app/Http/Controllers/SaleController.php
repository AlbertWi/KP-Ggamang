<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class SaleController extends Controller
{
    public function index()
    {
        $sales = \App\Models\Sale::with('items')
            ->where('branch_id', auth()->user()->branch_id)
            ->latest()
            ->get();
        return view('kepala_toko.sales.index', compact('sales'));
    }

    public function show($id)
    {
        $sale = \App\Models\Sale::with('items')->findOrFail($id);
        $sale = Sale::with(['items.product.brand', 'branch'])->findOrFail($id);
        return view('kepala_toko.sales.show', compact('sale'));
    }

    public function create()
    {
        $products = Product::all();
        return view('kepala_toko.sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        Log::info('Sale Store Request:', $request->all());

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.imei' => 'required|string|distinct',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $total = 0;
            $validItems = [];

            foreach ($validated['items'] as $item) {
                $inventory = \App\Models\InventoryItem::with('product')
                    ->where('imei', $item['imei'])
                    ->where('branch_id', auth()->user()->branch_id)
                    ->where('status', 'in_stock')
                    ->first();

                if (!$inventory) {
                    throw new \Exception("IMEI {$item['imei']} tidak ditemukan atau sudah terjual.");
                }

                $validItems[] = [
                    'inventory' => $inventory,
                    'price' => $item['price']
                ];

                $total += $item['price'];
            }

            $sale = Sale::create([
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id,
                'total' => $total,
            ]);

            foreach ($validItems as $item) {
                $inventory = $item['inventory'];
                $salePrice = $item['price'];

                // Tambah ke sale_items
                $sale->items()->create([
                    'product_id' => $inventory->product_id,
                    'imei' => $inventory->imei,
                    'price' => $salePrice,
                ]);

                // Update status inventory item
                $inventory->status = 'sold';
                $inventory->save();

                // Update qty pada tabel inventories
                $inventoryRecord = \App\Models\Inventory::where('branch_id', auth()->user()->branch_id)
                    ->where('product_id', $inventory->product_id)
                    ->first();

                if ($inventoryRecord && $inventoryRecord->qty > 0) {
                    $inventoryRecord->qty -= 1;
                    $inventoryRecord->save();
                    Log::info('Qty updated for product: ' . $inventory->product_id);
                }
            }

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Barang Keluar berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale store failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan Barang Keluar: ' . $e->getMessage());
        }
    }


    public function searchByImei(Request $request)
    {
        $imei = $request->query('imei');
        $user = auth()->user();

        if (!$imei) {
            return response()->json([
                'success' => false,
                'message' => 'IMEI tidak boleh kosong.'
            ]);
        }

        // Cari IMEI yang masih in_stock dan milik cabang user
        $inventory = \App\Models\InventoryItem::with('product')
            ->where('imei', $imei)
            ->where('branch_id', $user->branch_id)
            ->where('status', 'in_stock')
            ->first();

        if ($inventory) {
            return response()->json([
                'success' => true,
                'inventory' => [
                    'imei' => $inventory->imei,
                    'product' => [
                        'id' => $inventory->product->id,
                        'name' => $inventory->product->name,
                        'brand' => $inventory->product->brand,
                        'model' => $inventory->product->model,
                        'price' => $inventory->product->price,
                        'description' => $inventory->product->description ?? '',
                    ]
                ]
            ]);
        }
        $inventoryAny = \App\Models\InventoryItem::with('product')->where('imei', $imei)->first();

        if ($inventoryAny) {
            if ($inventoryAny->branch_id !== $user->branch_id) {
                $message = 'IMEI ditemukan, tapi bukan milik cabang Anda.';
            } elseif ($inventoryAny->status !== 'in_stock') {
                $message = "IMEI ditemukan, tapi status: {$inventoryAny->status}";
            } else {
                $message = 'IMEI tidak bisa digunakan.';
            }
        } else {
            $message = 'IMEI tidak ditemukan dalam database.';
        }

        return response()->json([
            'success' => false,
            'message' => $message
        ]);
    }
    public function laporanPenjualan(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;

        $query = Sale::with(['items.product', 'branch']);

        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereBetween('created_at', [
                Carbon::parse($tanggalAwal)->startOfDay(),
                Carbon::parse($tanggalAkhir)->endOfDay(),
            ]);
        }

        $penjualan = $query->get();

        // Hitung total dan laba
        $totalPendapatan = 0;
        $totalLaba = 0;

        foreach ($penjualan as $sale) {
            foreach ($sale->items as $item) {
                $hargaJual = $item->price;
                $hargaBeli = $item->inventoryItem->purchaseItem->price ?? 0;
                $totalPendapatan += $hargaJual;
                $totalLaba += ($hargaJual - $hargaBeli);
            }
        }

        return view('owner.laporan.index', compact('penjualan', 'tanggalAwal', 'tanggalAkhir', 'totalPendapatan', 'totalLaba'));
    }
}
