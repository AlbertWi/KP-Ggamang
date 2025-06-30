<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeImeiUniqueInInventoryItemsTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            // Pastikan null tetap diperbolehkan, dan hanya unique saat tidak null
            $table->string('imei')->nullable()->unique()->change();
        });
    }

    public function down()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropUnique(['imei']);
            $table->string('imei')->nullable()->change();
        });
    }
}
