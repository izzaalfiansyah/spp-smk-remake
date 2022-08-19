<?php

use App\Http\Controllers as Controller;
use App\Http\Middleware\Bearer;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/login', [Controller\UserController::class, 'login']);

Route::middleware(Bearer::class)->group(function () {
  Route::get('/kelas-jurusan-rombel', [Controller\JurusanController::class, 'kelas_jurusan_rombel']);
  Route::resource('/user', Controller\UserController::class);
  Route::resource('/jurusan', Controller\JurusanController::class);
  Route::post('/siswa/import', [Controller\SiswaController::class, 'import']);
  Route::get('/siswa/{nisn}', [Controller\SiswaController::class, 'show'])->where('nisn', '(.*)');
  Route::resource('/siswa', Controller\SiswaController::class);
  Route::resource('/biaya-lain', Controller\BiayaLainController::class);
  Route::prefix('/pembayaran')->group(function () {
    Route::post('/spp/batch', [Controller\PembayaranSppController::class, 'insertBatch']);
    Route::resource('/spp', Controller\PembayaranSppController::class);
    Route::resource('/biaya-lain', Controller\PembayaranBiayaLainController::class);
    Route::resource('/tabungan', Controller\PembayaranTabunganController::class);
  });
  Route::prefix('/laporan')->group(function () {
    Route::prefix('/spp')->group(function () {
      Route::get('/perhari', [Controller\LaporanSppController::class, 'perhari']);
      Route::get('/perbulan', [Controller\LaporanSppController::class, 'perbulan']);
      Route::get('/perkelas', [Controller\LaporanSppController::class, 'perkelas']);
      Route::get('/bagan', [Controller\LaporanSppController::class, 'bagan']);
      Route::get('/kekurangan', [Controller\LaporanSppController::class, 'kekurangan']);

      Route::get('/perhari/excel', [Controller\LaporanSppController::class, 'perhari_excel']);
      Route::get('/perbulan/excel', [Controller\LaporanSppController::class, 'perbulan_excel']);
      Route::get('/perkelas/excel', [Controller\LaporanSppController::class, 'perkelas_excel']);
      Route::get('/bagan/excel', [Controller\LaporanSppController::class, 'bagan_excel']);
      Route::get('/kekurangan/excel', [Controller\LaporanSppController::class, 'kekurangan_excel']);
      Route::get('/perhari/print', [Controller\LaporanSppController::class, 'perhari_print']);
      Route::get('/perbulan/print', [Controller\LaporanSppController::class, 'perbulan_print']);
      Route::get('/perkelas/print', [Controller\LaporanSppController::class, 'perkelas_print']);
      Route::get('/bagan/print', [Controller\LaporanSppController::class, 'bagan_print']);
      Route::get('/kekurangan/print', [Controller\LaporanSppController::class, 'kekurangan_print']);
    });
    Route::prefix('/biaya-lain')->group(function () {
      Route::get('/perhari', [Controller\LaporanBiayaLainController::class, 'perhari']);
      Route::get('/perkelas', [Controller\LaporanBiayaLainController::class, 'perkelas']);

      Route::get('/perhari/excel', [Controller\LaporanBiayaLainController::class, 'perhari_excel']);
      Route::get('/perkelas/excel', [Controller\LaporanBiayaLainController::class, 'perkelas_excel']);
      Route::get('/perhari/print', [Controller\LaporanBiayaLainController::class, 'perhari_print']);
      Route::get('/perkelas/print', [Controller\LaporanBiayaLainController::class, 'perkelas_print']);
    });
    Route::prefix('/tabungan')->group(function () {
      Route::get('/perhari', [Controller\LaporanTabunganController::class, 'perhari']);
      Route::get('/perbulan', [Controller\LaporanTabunganController::class, 'perbulan']);
      Route::get('/perkelas', [Controller\LaporanTabunganController::class, 'perkelas']);

      Route::get('/perhari/excel', [Controller\LaporanTabunganController::class, 'perhari_excel']);
      Route::get('/perbulan/excel', [Controller\LaporanTabunganController::class, 'perbulan_excel']);
      Route::get('/perkelas/excel', [Controller\LaporanTabunganController::class, 'perkelas_excel']);
      Route::get('/perhari/print', [Controller\LaporanTabunganController::class, 'perhari_print']);
      Route::get('/perbulan/print', [Controller\LaporanTabunganController::class, 'perbulan_print']);
      Route::get('/perkelas/print', [Controller\LaporanTabunganController::class, 'perkelas_print']);
    });
  });
});
