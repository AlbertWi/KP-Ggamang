<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    protected $fillable = [
        'from_branch_id', 'to_branch_id', 'product_id', 'qty', 'status', 'reason'
    ];

    public function fromBranch() {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch() {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}