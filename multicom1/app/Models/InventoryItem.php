<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'product_id', 'imei', 'status'];

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
