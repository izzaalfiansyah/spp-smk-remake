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
        Schema::create('pembayaran_tabungan', function (Blueprint $table) {
            $table->id();
            $table->string('siswa_nisn');
            $table->integer('user_id')->comment('operator');
            $table->integer('jumlah_bayar');
            $table->enum('status_tabungan', ['1', '2'])->comment('1: wajib, 2: pribadi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pembayaran_tabungan');
    }
};
