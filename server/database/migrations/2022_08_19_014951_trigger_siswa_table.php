<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::statement('create trigger siswa_delete_pembayaran_spp before delete on siswa for each row delete from pembayaran_spp where siswa_nisn = old.nisn');
        DB::statement('create trigger siswa_delete_pembayaran_biaya_lain before delete on siswa for each row delete from pembayaran_biaya_lain where siswa_nisn = old.nisn');
        DB::statement('create trigger siswa_delete_pembayaran_tabungan before delete on siswa for each row delete from pembayaran_tabungan where siswa_nisn = old.nisn');

        DB::statement('create trigger siswa_update_pembayaran_spp after update on siswa for each row update pembayaran_spp set siswa_nisn = new.nisn where siswa_nisn = old.nisn');
        DB::statement('create trigger siswa_update_pembayaran_biaya_lain after update on siswa for each row update pembayaran_biaya_lain set siswa_nisn = new.nisn where siswa_nisn = old.nisn');
        DB::statement('create trigger siswa_update_pembayaran_tabungan after update on siswa for each row update pembayaran_tabungan set siswa_nisn = new.nisn where siswa_nisn = old.nisn');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('drop trigger siswa_delete_pembayaran_spp');
        DB::statement('drop trigger siswa_delete_pembayaran_biaya_lain');
        DB::statement('drop trigger siswa_delete_pembayaran_tabungan');

        DB::statement('drop trigger siswa_update_pembayaran_spp');
        DB::statement('drop trigger siswa_update_pembayaran_biaya_lain');
        DB::statement('drop trigger siswa_update_pembayaran_tabungan');
    }
};
