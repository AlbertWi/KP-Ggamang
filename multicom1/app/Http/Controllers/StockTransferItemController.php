<?php

namespace App\Http\Controllers;

use App\Models\StockTransferItem;
use Illuminate\Http\Request;

class StockTransferItemController extends Controller
{
    public function index()
    {
        return StockTransferItem::with(['product'])->get();
    }

    public function show($id)
    {
        return StockTransferItem::with(['product'])->findOrFail($id);
    }
}
