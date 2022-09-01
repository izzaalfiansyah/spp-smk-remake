<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanBiayaLainController extends Controller
{
    public function perhari(Request $req)
    {
        $data = [];

        if ($tanggal = $req->_tanggal) {
            $builder = new \App\Models\PembayaranBiayaLain();
            $builder = $builder->select('pembayaran_biaya_lain.*', 'biaya_lain.jenis');
            $builder = $builder->leftJoin('biaya_lain', 'biaya_lain.id', '=', 'biaya_lain_id');
            $builder = $builder->whereDate('pembayaran_biaya_lain.created_at', $tanggal);

            if ($biaya_lain_id = $req->_biaya_lain_id) {
                $builder = $builder->where('biaya_lain.id', $biaya_lain_id);
            }

            if ($user_id = $req->_user_id) {
                $builder = $builder->where('user_id', $user_id);
            }

            $data = $builder->get();

            foreach ($data as $key => $item) {
                $data[$key]->siswa = DB::table('siswa')->where('nisn', $item->siswa_nisn)->first();
            }
        }

        return response()->json($data)
            ->header('X-Excel-Url', url('/laporan/biaya-lain/perhari/excel?' . http_build_query($req->all())))
            ->header('X-Print-Url', url('/laporan/biaya-lain/perhari/print?' . http_build_query($req->all())));
    }

    public function perhari_print(Request $req)
    {
        $data = $this->perhari($req)->original;

        $header = ['NO', 'NISN', 'NAMA SISWA', 'KELAS', 'JENIS PEMBAYARAN', 'TOTAL', 'OPERATOR'];
        $content = [];
        $total = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->siswa->nisn,
                $item->siswa->nama,
                $item->siswa->kelas . ' ' . $item->siswa->jurusan_kode . ' ' . $item->siswa->rombel,
                $item->jenis,
                $this->formatMoney($item->jumlah_bayar),
                $item->user->nama,
            ];

            $total += $item->jumlah_bayar;
        }

        $footer = ['', '', '', '', 'TOTAL', $this->formatMoney($total), ''];

        return $this->toPrint($content, $header, $footer);
    }

    public function perhari_excel(Request $req)
    {
        $data = $this->perhari($req)->original;

        $header = ['NO', 'NISN', 'NAMA SISWA', 'KELAS', 'JENIS PEMBAYARAN', 'TOTAL', 'OPERATOR'];
        $content = [];
        $total = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                "'{$item->siswa->nisn}'",
                $item->siswa->nama,
                $item->siswa->kelas . ' ' . $item->siswa->jurusan_kode . ' ' . $item->siswa->rombel,
                $item->jenis,
                $this->formatMoney($item->jumlah_bayar),
                $item->user->nama,
            ];

            $total += $item->jumlah_bayar;
        }

        $footer = ['', '', '', '', 'TOTAL', $this->formatMoney($total), ''];

        return $this->toExcel($content, $header, $footer, 'laporan-biaya-lain-' . date('Y-m-d'));
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

            if ($biaya_lain_id = $req->_biaya_lain_id) {
                $biaya_lain = DB::table('biaya_lain')->where('id', $biaya_lain_id)->where([
                    ['jurusan_data', 'like', '%"' . $jurusan_kode . '"%'],
                    ['kelas_data', 'like', '%"' . $kelas . '"%']
                ])->get();
            } else {
                $biaya_lain = DB::table('biaya_lain')->where([
                    ['jurusan_data', 'like', '%"' . $jurusan_kode . '"%'],
                    ['kelas_data', 'like', '%"' . $kelas . '"%']
                ])->get();
            }

            foreach ($data as $key => $item) {
                foreach ($biaya_lain as $keyBl => $itemBl) {
                    $terbayar = DB::table('pembayaran_biaya_lain')
                        ->select(
                            DB::raw('sum(jumlah_bayar) as total')
                        )
                        ->where('biaya_lain_id', $itemBl->id)
                        ->where('siswa_nisn', $item->nisn)
                        ->first();

                    $data_biaya_lain[$keyBl] = (object) [
                        'jenis' => $itemBl->jenis,
                        'jumlah_bayar' => $itemBl->jumlah_bayar,
                        'terbayar' => ((int) $terbayar?->total)
                    ];
                }

                $data[$key]->biaya_lain = $data_biaya_lain;
            }
        }

        return response()->json($data)
            ->header('X-Excel-Url', url('/laporan/biaya-lain/perkelas/excel?' . http_build_query($req->all())))
            ->header('X-Print-Url', url('/laporan/biaya-lain/perkelas/print?' . http_build_query($req->all())));
    }

    public function perkelas_print(Request $req)
    {
        $data = $this->perkelas($req)->original;

        $headerBiayaLain = ['', '', ''];
        foreach ($data[0]->biaya_lain as $item) {
            array_push($headerBiayaLain, strtoupper($item->jenis));
            array_push($headerBiayaLain, '');
        }

        $header = ['NO', 'NISN', 'NAMA'];
        foreach ($data[0]->biaya_lain as $item) {
            array_push($header, 'TERBAYAR');
            array_push($header, 'KEKURANGAN');
        }

        $content = [
            $headerBiayaLain,
            $header
        ];

        foreach ($data as $key => $item) {
            $itemContent = [
                $key + 1,
                $item->nisn,
                $item->nama,
            ];

            foreach ($data[$key]->biaya_lain as $bl) {
                array_push($itemContent, $this->formatMoney($bl->terbayar));
                array_push($itemContent, $this->formatMoney($bl->jumlah_bayar - $bl->terbayar));
            }

            array_push($content, $itemContent);
        }

        return $this->toPrint($content);
    }

    public function perkelas_excel(Request $req)
    {
        $data = $this->perkelas($req)->original;

        $headerBiayaLain = ['', '', ''];
        foreach ($data[0]->biaya_lain as $item) {
            array_push($headerBiayaLain, strtoupper($item->jenis));
            array_push($headerBiayaLain, '');
        }

        $header = ['NO', 'NISN', 'NAMA'];
        foreach ($data[0]->biaya_lain as $item) {
            array_push($header, 'TERBAYAR');
            array_push($header, 'KEKURANGAN');
        }

        $content = [
            $headerBiayaLain,
            $header
        ];

        foreach ($data as $key => $item) {
            $itemContent = [
                $key + 1,
                "'{$item->nisn}'",
                $item->nama,
            ];

            foreach ($data[$key]->biaya_lain as $bl) {
                array_push($itemContent, $this->formatMoney($bl->terbayar));
                array_push($itemContent, $this->formatMoney($bl->jumlah_bayar - $bl->terbayar));
            }

            array_push($content, $itemContent);
        }

        return $this->toExcel($content, [], [], 'laporan-biaya-lain-' . $req->_kelas_jurusan_rombel);
    }
}
