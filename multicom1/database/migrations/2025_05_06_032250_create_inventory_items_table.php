<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryItemsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('imei')->nullable()->unique();
            $table->enum('status', ['in_stock', 'sold', 'transferred'])->default('in_stock');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
}
