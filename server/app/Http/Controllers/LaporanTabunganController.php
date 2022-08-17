<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanTabunganController extends Controller
{
    public function perhari(Request $req)
    {
        $data = [];

        if ($tanggal = $req->_tanggal) {
            $builder = new \App\Models\PembayaranTabungan();
            $builder = $builder->whereDate('created_at', $tanggal);

            $data = $builder->get();

            foreach ($data as $key => $item) {
                $data[$key]->siswa = DB::table('siswa')->where('nisn', $item->siswa_nisn)->first();
            }
        }

        return response()->json($data)
            ->header('X-Excel-Url', url('/laporan/tabungan/perhari/excel?' . http_build_query($req->all())))
            ->header('X-Print-Url', url('/laporan/tabungan/perhari/print?' . http_build_query($req->all())));
    }

    public function perhari_print(Request $req)
    {
        $data = $this->perhari($req)->original;

        $header = ['NO', 'NISN', 'NAMA SISWA', 'KELAS', 'ID', 'JENIS TABUNGAN', 'NOMINAL', 'OPERATOR'];
        $content = [];
        $total = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->siswa->nisn,
                $item->siswa->nama,
                $item->siswa->kelas . ' ' . $item->siswa->jurusan_kode . ' ' . $item->siswa->rombel,
                $item->jumlah_bayar > 0 ? '1' : '0',
                $item->status_tabungan_detail,
                $this->formatMoney($item->jumlah_bayar),
                $item->user->nama
            ];

            $total += $item->jumlah_bayar;
        }

        $footer = ['', '', '', '', '', 'TOTAL', $this->formatMoney($total), ''];

        return $this->toPrint($content, $header, $footer);
    }

    public function perhari_excel(Request $req)
    {
        $data = $this->perhari($req)->original;

        $header = ['NO', 'NISN', 'NAMA SISWA', 'KELAS', 'ID', 'JENIS TABUNGAN', 'NOMINAL', 'OPERATOR'];
        $content = [];
        $total = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                "'{$item->siswa->nisn}'",
                $item->siswa->nama,
                $item->siswa->kelas . ' ' . $item->siswa->jurusan_kode . ' ' . $item->siswa->rombel,
                $item->jumlah_bayar > 0 ? '1' : '0',
                $item->status_tabungan_detail,
                $this->formatMoney($item->jumlah_bayar),
                $item->user->nama
            ];

            $total += $item->jumlah_bayar;
        }

        $footer = ['', '', '', '', '', 'TOTAL', $this->formatMoney($total), ''];

        return $this->toExcel($content, $header, $footer, 'laporan-tabungan-' . date('Y-m-d'));
    }

    public function perbulan(Request $req)
    {
        $data = [];

        if ($bulan = $req->_bulan) {

            function getData($operator = '>', $bulan)
            {
                $builder = new \App\Models\PembayaranTabungan();
                $builder = $builder->select(
                    DB::raw('count(id) as jumlah_transaksi'),
                    'siswa.kelas as siswa_kelas',
                    'siswa.jurusan_kode as siswa_jurusan_kode',
                    'siswa.rombel as siswa_rombel',
                    DB::raw('sum(jumlah_bayar) as total_transaksi')
                );
                $builder = $builder->leftJoin('siswa', 'siswa.nisn', '=', 'siswa_nisn');
                $builder = $builder->groupBy('siswa.kelas');
                $builder = $builder->groupBy('siswa.jurusan_kode');
                $builder = $builder->groupBy('siswa.rombel');
                $builder = $builder->where('jumlah_bayar', $operator, 0);
                $builder = $builder->whereMonth('created_at', $bulan);

                return $builder->get();
            }

            $data_tambah = getData('>', $bulan);
            $data_kurang = getData('<', $bulan);

            foreach ($data_tambah as $key => $item) {
                $kekurangan = (object) [
                    'jumlah_transaksi' => 0,
                    'total_transaksi' => 0,
                ];

                foreach ($data_kurang as $kurang) {
                    if ($kurang->siswa_kelas == $item->siswa_kelas && $kurang->siswa_jurusan_kode == $item->siswa_jurusan_kode && $kurang->siswa_rombel == $item->siswa_rombel) {
                        $kekurangan = $kurang;
                    }
                }

                $data[$key] = $item;
                $data[$key]->jumlah_tambah = (int) $item?->jumlah_transaksi;
                $data[$key]->total_tambah = (int) $item?->total_transaksi;
                $data[$key]->jumlah_ambil = (int) $kekurangan?->jumlah_transaksi;
                $data[$key]->total_ambil = (int) $kekurangan?->total_transaksi;
            }
        }

        return response()->json($data)
            ->header('X-Excel-Url', url('/laporan/tabungan/perbulan/excel?' . http_build_query($req->all())))
            ->header('X-Print-Url', url('/laporan/tabungan/perbulan/print?' . http_build_query($req->all())));
    }

    public function perbulan_print(Request $req)
    {
        $data = $this->perbulan($req)->original;

        $header = ['NO', 'KELAS', 'JUMLAH MENABUNG', 'SALDO MENABUNG', 'JUMLAH MENGAMBIL', 'SALDO MENGAMBIL'];
        $content = [];
        $totalMenabung = 0;
        $totalMengambil = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->siswa_kelas . ' ' . $item->siswa_jurusan_kode . ' ' . $item->rombel,
                $item->jumlah_tambah,
                $this->formatMoney($item->total_tambah),
                $item->jumlah_ambil,
                $this->formatMoney($item->total_ambil),
            ];

            $totalMenabung += $item->total_tambah;
            $totalMengambil += $item->total_ambil;
        }

        $footer = ['', '', 'TOTAL MENABUNG', $this->formatMoney($totalMenabung), 'TOTAL MENGAMBIL', $this->formatMoney($totalMengambil)];

        return $this->toPrint($content, $header, $footer);
    }

    public function perbulan_excel(Request $req)
    {
        $data = $this->perbulan($req)->original;

        $header = ['NO', 'KELAS', 'JUMLAH MENABUNG', 'SALDO MENABUNG', 'JUMLAH MENGAMBIL', 'SALDO MENGAMBIL'];
        $content = [];
        $totalMenabung = 0;
        $totalMengambil = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->siswa_kelas . ' ' . $item->siswa_jurusan_kode . ' ' . $item->rombel,
                $item->jumlah_tambah,
                $this->formatMoney($item->total_tambah),
                $item->jumlah_ambil,
                $this->formatMoney($item->total_ambil),
            ];

            $totalMenabung += $item->total_tambah;
            $totalMengambil += $item->total_ambil;
        }

        $footer = ['', '', 'TOTAL MENABUNG', $this->formatMoney($totalMenabung), 'TOTAL MENGAMBIL', $this->formatMoney($totalMengambil)];

        return $this->toExcel($content, $header, $footer, 'laporan-tabungan-' . date('Y-M'));
    }

    public function perkelas(Request $req)
    {
        $data = [];

        if ($kelas_jurusan_rombel = $req->_kelas_jurusan_rombel) {
            $request = explode('-', $kelas_jurusan_rombel);
            $kelas = $request[0];
            $jurusan_kode = $request[1];
            $rombel = $request[2];

            $builder = new \App\Models\Siswa();
            $builder = $builder->where(function ($query) use ($kelas, $jurusan_kode, $rombel) {
                $query->where('kelas', $kelas)
                    ->where('jurusan_kode', $jurusan_kode)
                    ->where('rombel', $rombel);
            });

            $data = $builder->get();

            foreach ($data as $key => $item) {
                $data[$key]->tabungan_wajib = (int) DB::table('pembayaran_spp')->select(DB::raw('sum(tabungan_wajib) as total'))->where('siswa_nisn', $item->nisn)->first()?->total;
                $data[$key]->tabungan_pribadi = (int) DB::table('pembayaran_tabungan')->select(DB::raw('sum(jumlah_bayar) as total'))->where('siswa_nisn', $item->nisn)->first()?->total;
            }
        }

        return response()->json($data)
            ->header('X-Excel-Url', url('/laporan/tabungan/perkelas/excel?' . http_build_query($req->all())))
            ->header('X-Print-Url', url('/laporan/tabungan/perkelas/print?' . http_build_query($req->all())));
    }

    public function perkelas_print(Request $req)
    {
        $data = $this->perkelas($req)->original;

        $header = ['NO', 'NISN', 'NAMA', 'TABUNGAN WAJIB', 'TABUNGAN PRIBADI', 'TOTAL'];
        $content = [];
        $total = 0;
        $totalWajib = 0;
        $totalPribadi = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->nisn,
                $item->nama,
                $this->formatMoney($item->tabungan_wajib),
                $this->formatMoney($item->tabungan_pribadi),
                $this->formatMoney($item->tabungan_wajib + $item->tabungan_pribadi),
            ];

            $totalWajib += $item->tabungan_wajib;
            $totalPribadi += $item->tabungan_pribadi;
            $total += $item->tabungan_wajib + $item->tabungan_pribadi;
        }

        $footer = ['', '', 'TOTAL', $this->formatMoney($totalWajib), $this->formatMoney($totalPribadi), $this->formatMoney($total)];

        return $this->toPrint($content, $header, $footer);
    }

    public function perkelas_excel(Request $req)
    {
        $data = $this->perkelas($req)->original;

        $header = ['NO', 'NISN', 'NAMA', 'TABUNGAN WAJIB', 'TABUNGAN PRIBADI', 'TOTAL'];
        $content = [];
        $total = 0;
        $totalWajib = 0;
        $totalPribadi = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                "'{$item->nisn}'",
                $item->nama,
                $this->formatMoney($item->tabungan_wajib),
                $this->formatMoney($item->tabungan_pribadi),
                $this->formatMoney($item->tabungan_wajib + $item->tabungan_pribadi),
            ];

            $totalWajib += $item->tabungan_wajib;
            $totalPribadi += $item->tabungan_pribadi;
            $total += $item->tabungan_wajib + $item->tabungan_pribadi;
        }

        $footer = ['', '', 'TOTAL', $this->formatMoney($totalWajib), $this->formatMoney($totalPribadi), $this->formatMoney($total)];

        return $this->toExcel($content, $header, $footer, 'laporan-tabungan-' . $req->_kelas_jurusan_rombel);
    }
}
