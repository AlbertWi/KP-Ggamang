<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    use HasFactory;

    protected $fillable = ['stock_transfer_id', 'inventory_item_id'];

    public function stockTransfer() {
        return $this->belongsTo(StockTransfer::class);
    }

    public function inventoryItem() {
        return $this->belongsTo(InventoryItem::class);
    }
    
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
