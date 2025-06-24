<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'address'];

    public function users() {
        return $this->hasOne(User::class);
    }

    public function inventoryItems() {
        return $this->hasMany(InventoryItem::class);
    }
}
