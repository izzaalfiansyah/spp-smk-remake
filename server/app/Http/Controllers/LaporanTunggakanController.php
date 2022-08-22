<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanTunggakanController extends Controller
{
    public function index()
    {
        $pembayaran_spp = DB::table('pembayaran_spp')
            ->select(
                DB::raw('count(pembayaran_spp.id) as terbayar'),
                'siswa.kelas',
                'siswa.jurusan_kode',
                'siswa.rombel',
            )
            ->leftJoin('siswa', 'siswa.nisn', '=', 'siswa_nisn')
            ->groupBy('siswa.jurusan_kode', 'siswa.kelas', 'siswa.rombel')
            ->get();

        foreach ($pembayaran_spp as $key => $item) {
            $pembayaran_spp[$key]->pembayaran_total = DB::table('siswa')->where([
                'jurusan_kode' => $item->jurusan_kode,
                'kelas' => $item->kelas,
                'rombel' => $item->rombel,
            ])->count() * 12;
            $pembayaran_spp[$key]->jumlah_spp = (int) DB::table('jurusan')->where('kode', $item->jurusan_kode)->first()?->jumlah_spp;
            $pembayaran_spp[$key]->jumlah_tabungan = 15000;
        }

        return response()->json($pembayaran_spp)
            ->header('X-Print-Url', url('/laporan/tunggakan/print'))
            ->header('X-Excel-Url', url('/laporan/tunggakan/excel'));
    }

    public function print()
    {
        $data = $this->index()->original;

        $header = ['KELAS', 'JUMLAH TUNGGAKAN', 'TUNGGAKAN SPP', 'TUNGGAKAN TABSIS'];

        $content = [];
        $total_spp = 0;
        $total_tabungan = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $item->kelas . ' ' . $item->jurusan_kode . ' ' . $item->rombel,
                $item->pembayaran_total - $item->terbayar,
                $this->formatMoney(($item->pembayaran_total - $item->terbayar) * $item->jumlah_spp),
                $this->formatMoney(($item->pembayaran_total - $item->terbayar) * $item->jumlah_tabungan),
            ];

            $total_spp += ($item->pembayaran_total - $item->terbayar) * $item->jumlah_spp;
            $total_tabungan += ($item->pembayaran_total - $item->terbayar) * $item->jumlah_tabungan;
        }

        $footer = ['', 'TOTAL', $this->formatMoney($total_spp), $this->formatMoney($total_tabungan)];

        return $this->toPrint($content, $header, $footer);
    }

    public function excel()
    {
        $data = $this->index()->original;

        $header = ['KELAS', 'JUMLAH TUNGGAKAN', 'TUNGGAKAN SPP', 'TUNGGAKAN TABSIS'];

        $content = [];
        $total_spp = 0;
        $total_tabungan = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $item->kelas . ' ' . $item->jurusan_kode . ' ' . $item->rombel,
                $item->pembayaran_total - $item->terbayar,
                $this->formatMoney(($item->pembayaran_total - $item->terbayar) * $item->jumlah_spp),
                $this->formatMoney(($item->pembayaran_total - $item->terbayar) * $item->jumlah_tabungan),
            ];

            $total_spp += ($item->pembayaran_total - $item->terbayar) * $item->jumlah_spp;
            $total_tabungan += ($item->pembayaran_total - $item->terbayar) * $item->jumlah_tabungan;
        }

        $footer = ['', 'TOTAL', $this->formatMoney($total_spp), $this->formatMoney($total_tabungan)];

        return $this->toExcel($content, $header, $footer, 'laporan-tunggakan');
    }
}
