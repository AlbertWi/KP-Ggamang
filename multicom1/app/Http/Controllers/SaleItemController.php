<?php

namespace App\Http\Controllers;

use App\Models\SaleItem;
use Illuminate\Http\Request;

class SaleItemController extends Controller
{
    public function index()
    {
        return SaleItem::with(['product'])->get();
    }

    public function show($id)
    {
        return SaleItem::with(['product'])->findOrFail($id);
    }
}
