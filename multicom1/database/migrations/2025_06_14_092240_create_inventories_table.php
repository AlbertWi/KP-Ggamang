<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('qty')->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'product_id']); // Satu produk hanya boleh sekali per cabang
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}
