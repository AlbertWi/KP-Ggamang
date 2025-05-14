<?php

namespace App\Http\Controllers;

use App\Models\PurchaseItem;
use Illuminate\Http\Request;

class PurchaseItemController extends Controller
{
    public function index()
    {
        return PurchaseItem::with(['product'])->get();
    }

    public function show($id)
    {
        return PurchaseItem::with(['product'])->findOrFail($id);
    }
}
