<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'branch_id', 'supplier_id', 'purchase_date'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function items() {
        return $this->hasMany(PurchaseItem::class);
    }
    public function branch()
    {
    return $this->belongsTo(Branch::class);
    }
    public function isImeiComplete()
    {
    foreach ($this->items as $item) {
        foreach ($item->inventoryItems as $inv) {
            if (is_null($inv->imei)) {
                return false;
            }
        }
    }
    return true;
    }
}
