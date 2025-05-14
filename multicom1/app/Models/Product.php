<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['brand', 'model'];

    public function inventoryItems() {
        return $this->hasMany(InventoryItem::class);
    }
}
