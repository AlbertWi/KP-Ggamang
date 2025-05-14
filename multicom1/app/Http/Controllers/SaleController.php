<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        return Sale::with(['items'])->get();
    }

    public function show($id)
    {
        return Sale::with(['items'])->findOrFail($id);
    }
}
