<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name','brand_id'];

    public function inventoryItems() {
        return $this->hasMany(InventoryItem::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
