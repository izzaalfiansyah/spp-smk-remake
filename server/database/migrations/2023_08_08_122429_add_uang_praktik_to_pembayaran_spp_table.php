<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pembayaran_spp', function (Blueprint $table) {
            $table->integer('uang_praktik')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pembayaran_spp', function (Blueprint $table) {
            $table->dropColumn('uang_praktik');
        });
    }
};
