<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceToSaleItemsTable extends Migration
{
    public function up()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->after('imei');
        });
    }

    public function down()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
}