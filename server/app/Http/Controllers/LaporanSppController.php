<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanSppController extends Controller
{
    public function perhari(Request $req)
    {
        $data = [];

        if ($tanggal = $req->_tanggal) {
            $builder = new \App\Models\PembayaranSpp();
            $builder = $builder->select(
                'siswa_nisn',
                DB::raw('count(id) as total_bulan'),
                DB::raw('cast(sum(jumlah_bayar) as unsigned) as total_bayar'),
                DB::raw('cast(sum(tabungan_wajib) as unsigned) as total_tabungan'),
            );
            $builder = $builder->groupBy('siswa_nisn');
            $builder = $builder->whereDate('created_at', $tanggal);

            if ($user_id = $req->_user_id) {
                $builder = $builder->where('user_id', $user_id);
            }

            $data = $builder->get();

            foreach ($data as $key => $item) {
                $data[$key]->total_bulan = $item->total_bulan . ' Bulan';
                $data[$key]->siswa = DB::table('siswa')->where('nisn', $item->siswa_nisn)->first();
                $data[$key]->user_id = DB::table('pembayaran_spp')->where('siswa_nisn', $item->siswa_nisn)->whereDate('created_at', $tanggal)->first()->user_id;
                $data[$key]->operator = DB::table('user')->where('id', $data[$key]->user_id)->first();
            }
        }

        return response()->json($data)
            ->header('X-Excel-Url', url('/laporan/spp/perhari/excel?' . http_build_query($req->all())))
            ->header('X-Print-Url', url('/laporan/spp/perhari/print?' . http_build_query($req->all())));
    }

    public function perhari_print(Request $req)
    {
        $data = $this->perhari($req)->original;

        $content = [];
        $total = $total_bulan = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->siswa->nisn,
                $item->siswa->nama,
                $item->siswa->kelas . ' ' . $item->siswa->jurusan_kode . ' ' . $item->siswa->rombel,
                $item->total_bulan,
                $this->formatMoney($item->total_bayar + $item->total_tabungan),
            ];
            $total_bulan += (int) str_replace(' Bulan', '', $item->total_bulan);
            $total += $item->total_bayar + $item->total_tabungan;
        }

        return $this->toPrint($content, ['NO', 'NISN', 'NAMA SISWA', 'KELAS', 'BULAN', 'TOTAL'], ['', '', '', 'TOTAL', $total_bulan . ' Bulan', $this->formatMoney($total)]);
    }

    public function perhari_excel(Request $req)
    {
        $data = $this->perhari($req)->original;

        $content = [];
        $total = $total_bulan = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                "'{$item->siswa->nisn}'",
                $item->siswa->nama,
                $item->siswa->kelas . ' ' . $item->siswa->jurusan_kode . ' ' . $item->siswa->rombel,
                $item->total_bulan,
                $this->formatMoney($item->total_bayar + $item->total_tabungan),
            ];
            $total_bulan += (int) str_replace(' Bulan', '', $item->total_bulan);
            $total += $item->total_bayar + $item->total_tabungan;
        }

        return $this->toExcel($content, ['NO', 'NISN', 'NAMA SISWA', 'KELAS', 'BULAN', 'TOTAL'], ['', '', '', 'TOTAL', $total_bulan . ' Bulan', $this->formatMoney($total)], 'laporan-spp-' . date('Y-m-d'));
    }

    public function perbulan(Request $req)
    {
        $data = [];

        if (($tanggal_awal = $req->_tanggal_awal) && ($tanggal_akhir = $req->_tanggal_akhir)) {
            $builder = new \App\Models\PembayaranSpp();
            $builder = $builder->select(
                DB::raw('count(pembayaran_spp.id) as jumlah_pembayaran'),
                'siswa.kelas as kelas',
                'siswa.jurusan_kode as jurusan_kode',
                'siswa.rombel as rombel',
                DB::raw('cast(sum(jumlah_bayar) as unsigned) as total_bayar'),
                DB::raw('cast(sum(tabungan_wajib) as unsigned) as total_tabungan'),
            );
            $builder = $builder->leftJoin('siswa', 'siswa.nisn', '=', 'pembayaran_spp.siswa_nisn');
            $builder = $builder->groupBy('siswa.kelas');
            $builder = $builder->groupBy('siswa.jurusan_kode');
            $builder = $builder->groupBy('siswa.rombel');
            $builder = $builder->whereDate('pembayaran_spp.created_at', '>=', $tanggal_awal);
            $builder = $builder->whereDate('pembayaran_spp.created_at', '<=', $tanggal_akhir);


            if ($user_id = $req->_user_id) {
                $builder = $builder->where('user_id', $user_id);
            }

            $data = $builder->get();

            foreach ($data as $key => $item) {
                $jurusan = DB::table('jurusan')->where('kode', $item->jurusan_kode)->first();
                $siswaKeringanan = DB::table('siswa')
                    ->where('diskon_spp', '>', '0')
                    ->where('jurusan_kode', $item->jurusan_kode)
                    ->where('kelas', $item->kelas)
                    ->where('rombel', $item->rombel)
                    ->get();

                $keringanan = (object) [
                    'jumlah' => 0,
                    'uang' => 0,
                    'total' => 0,
                ];

                foreach ($siswaKeringanan as $s) {
                    $uangKeringanan = (($jurusan->jumlah_spp * $s->diskon_spp / 100) - ($jurusan->kategori == '2' ? ($s->diskon_spp > 50 ? 10000 : 0) : 0));

                    $keringanan->uang += $uangKeringanan;
                    $keringanan->total += $jurusan->jumlah_spp - $uangKeringanan;
                    $keringanan->jumlah += 1;
                }

                $data[$key]->keringanan = $keringanan;
            }
        }

        return response()->json($data)
            ->header('X-Excel-Url', url('/laporan/spp/perbulan/excel?' . http_build_query($req->all())))
            ->header('X-Print-Url', url('/laporan/spp/perbulan/print?' . http_build_query($req->all())));
    }

    public function perbulan_print(Request $req)
    {
        $data = $this->perbulan($req)->original;

        $content = [];
        $total = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->kelas . ' ' . $item->jurusan_kode . ' ' . $item->rombel,
                $item->jumlah_pembayaran . ' Transaksi',
                $item->keringanan->jumlah . ' Transaksi',
                $this->formatMoney($item->keringanan->uang),
                $this->formatMoney($item->keringanan->total),
                $this->formatMoney($item->total_bayar + $item->total_tabungan),
            ];
            $total += $item->total_bayar + $item->total_tabungan;
        }

        return $this->toPrint($content, ['NO', 'KELAS', 'JUMLAH TOTAL', 'JUMLAH KERINGANAN', 'UANG KERINGANAN', 'TOTAL SPP KERINGANAN', 'TOTAL'], ['', '', '', '', '', 'TOTAL', $this->formatMoney($total)]);
    }

    public function perbulan_excel(Request $req)
    {
        $data = $this->perbulan($req)->original;

        $content = [];
        $total = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->kelas . ' ' . $item->jurusan_kode . ' ' . $item->rombel,
                $item->jumlah_pembayaran . ' Transaksi',
                $item->keringanan->jumlah . ' Transaksi',
                $this->formatMoney($item->keringanan->uang),
                $this->formatMoney($item->keringanan->total),
                $this->formatMoney($item->total_bayar + $item->total_tabungan),
            ];
            $total += $item->total_bayar + $item->total_tabungan;
        }

        return $this->toExcel($content, ['NO', 'KELAS', 'JUMLAH TOTAL', 'JUMLAH KERINGANAN', 'UANG KERINGANAN', 'TOTAL SPP KERINGANAN', 'TOTAL'], ['', '', '', '', '', 'TOTAL', $this->formatMoney($total)], 'laporan-spp-' . date('Y-M'));
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
                $spp = DB::table('pembayaran_spp')
                    ->select(
                        DB::raw('count(id) as jumlah_pembayaran'),
                        DB::raw('cast(sum(jumlah_bayar) as unsigned) as total_bayar'),
                        DB::raw('cast(sum(tabungan_wajib) as unsigned) as total_tabungan'),
                    )
                    ->where('siswa_nisn', $item->nisn)
                    ->where('status_kelas', $kelas)
                    ->first();
                $data[$key]->spp = $spp;
            }
        }

        return response()->json($data)
            ->header('X-Excel-Url', url('/laporan/spp/perkelas/excel?' . http_build_query($req->all())))
            ->header('X-Print-Url', url('/laporan/spp/perkelas/print?' . http_build_query($req->all())));
    }

    public function perkelas_print(Request $req)
    {
        $data = $this->perkelas($req)->original;

        $content = [];
        $total = 0;
        $total_jumlah = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->nisn,
                $item->nama,
                $item->spp->jumlah_pembayaran,
                $this->formatMoney($item->spp->total_bayar + $item->spp->total_tabungan),
            ];
            $total_jumlah += $item->spp->jumlah_pembayaran;
            $total += $item->spp->total_bayar + $item->spp->total_tabungan;
        }

        return $this->toPrint($content, ['NO', 'NISN', 'NAMA SISWA', 'TERBAYAR', 'TOTAL'], ['', '', 'TOTAL', $total_jumlah, $this->formatMoney($total)]);
    }

    public function perkelas_excel(Request $req)
    {
        $data = $this->perkelas($req)->original;

        $content = [];
        $total = 0;
        $total_jumlah = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                "'{$item->nisn}'",
                $item->nama,
                $item->spp->jumlah_pembayaran,
                $this->formatMoney($item->spp->total_bayar + $item->spp->total_tabungan),
            ];
            $total_jumlah += $item->spp->jumlah_pembayaran;
            $total += $item->spp->total_bayar + $item->spp->total_tabungan;
        }

        return $this->toExcel($content, ['NO', 'NISN', 'NAMA SISWA', 'TERBAYAR', 'TOTAL'], ['', '', 'TOTAL', $total_jumlah, $this->formatMoney($total)], 'laporan-spp-' . $req->_kelas_jurusan_rombel);
    }

    public function bagan(Request $req)
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
                $spp = DB::table('pembayaran_spp')->select('bulan')->where('siswa_nisn', $item->nisn)->where('status_kelas', $kelas)->get();
                $data[$key]->spp = $spp;
            }
        }

        return response()->json($data)
            ->header('X-Excel-Url', url('/laporan/spp/bagan/excel?' . http_build_query($req->all())))
            ->header('X-Print-Url', url('/laporan/spp/bagan/print?' . http_build_query($req->all())));;
    }

    public function bagan_print(Request $req)
    {
        $data = $this->bagan($req)->original;

        $bulan = [
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
        ];
        $content = [];

        $header = ['NO', 'NISN', 'NAMA SISWA'];
        foreach ($bulan as $item) {
            array_push($header, strtoupper(substr($item, 0, 3)));
        }
        array_push($header, 'TOTAL');

        foreach ($data as $key => $item) {
            $itemContent = [
                $key + 1,
                $item->nisn,
                $item->nama,
            ];
            foreach ($bulan as $b) {
                $value = strpos(json_encode($item->spp), $b) ? '1' : '0';
                array_push($itemContent, $value);
            }
            array_push($itemContent, count($item->spp));

            $content[] = $itemContent;
        }

        return $this->toPrint($content, $header, [], strtoupper("Laporan Bagan SPP kelas " . str_replace('-', ' ', $req->_kelas_jurusan_rombel)));
    }

    public function bagan_excel(Request $req)
    {
        $data = $this->bagan($req)->original;

        $bulan = [
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
        ];
        $content = [];

        $header = ['NO', 'NISN', 'NAMA SISWA'];
        foreach ($bulan as $item) {
            array_push($header, strtoupper(substr($item, 0, 3)));
        }
        array_push($header, 'TOTAL');

        foreach ($data as $key => $item) {
            $itemContent = [
                $key + 1,
                "'{$item->nisn}'",
                $item->nama,
            ];
            foreach ($bulan as $b) {
                $value = strpos(json_encode($item->spp), $b) ? '1' : '0';
                array_push($itemContent, $value);
            }
            array_push($itemContent, count($item->spp));

            $content[] = $itemContent;
        }

        return $this->toExcel($content, $header, [], 'laporan-bagan-spp-' . $req->_kelas_jurusan_rombel);
    }

    public function kekurangan(Request $req)
    {
        $data = [];

        if ($kelas_jurusan_rombel = $req->_kelas_jurusan_rombel) {
            $request = explode('-', $kelas_jurusan_rombel);
            $kelas = $request[0];
            $jurusan_kode = $request[1];
            $rombel = $request[2];

            $builder = new \App\Models\Siswa();
            $builder = $builder->where(function ($query) use ($kelas, $jurusan_kode, $rombel) {
                $query->where('siswa.kelas', $kelas)
                    ->where('siswa.jurusan_kode', $jurusan_kode)
                    ->where('siswa.rombel', $rombel);
            });

            $data = $builder->get();

            foreach ($data as $key => $item) {
                $totalSpp = DB::table('pembayaran_spp')->select('bulan')->where('siswa_nisn', $item->nisn)->where('status_kelas', $kelas)->count();
                $data[$key]->kekurangan = (12 - $totalSpp);
            }
        }

        return response()->json($data)
            ->header('X-Excel-Url', url('/laporan/spp/kekurangan/excel?' . http_build_query($req->all())))
            ->header('X-Print-Url', url('/laporan/spp/kekurangan/print?' . http_build_query($req->all())));;
    }

    public function kekurangan_print(Request $req)
    {
        $data = $this->kekurangan($req)->original;

        $header = ['NO', 'NISN', 'NAMA SISWA', 'KEKURANGAN', 'JUMLAH KEKURANGAN'];
        $content = [];
        $total = 0;
        $totalKekurangan = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->nisn,
                $item->nama,
                $item->kekurangan,
                $this->formatMoney($item->kekurangan * (($item->jurusan->jumlah_spp - ($item->jurusan->jumlah_spp * $item->diskon_spp / 100))) - ($item->jurusan->kategori == '2' ? ($item->diskon_spp > 50 ? 10000 : 0) : 0)),
            ];

            $total += $item->kekurangan * (($item->jurusan->jumlah_spp - ($item->jurusan->jumlah_spp * $item->diskon_spp / 100))) - ($item->jurusan->kategori == '2' ? ($item->diskon_spp > 50 ? 10000 : 0) : 0);
            $totalKekurangan += $item->kekurangan;
        }

        $footer = ['', '', 'TOTAL', $totalKekurangan, $this->formatMoney($total)];

        return $this->toPrint($content, $header, $footer);
    }

    public function kekurangan_excel(Request $req)
    {
        $data = $this->kekurangan($req)->original;

        $header = ['NO', 'NISN', 'NAMA SISWA', 'KEKURANGAN', 'JUMLAH KEKURANGAN'];
        $content = [];
        $total = 0;
        $totalKekurangan = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                "'{$item->nisn}'",
                $item->nama,
                $item->kekurangan,
                $this->formatMoney($item->kekurangan * (($item->jurusan->jumlah_spp - ($item->jurusan->jumlah_spp * $item->diskon_spp / 100))) - ($item->jurusan->kategori == '2' ? ($item->diskon_spp > 50 ? 10000 : 0) : 0)),
            ];

            $total += $item->kekurangan * (($item->jurusan->jumlah_spp - ($item->jurusan->jumlah_spp * $item->diskon_spp / 100))) - ($item->jurusan->kategori == '2' ? ($item->diskon_spp > 50 ? 10000 : 0) : 0);
            $totalKekurangan += $item->kekurangan;
        }

        $footer = ['', '', 'TOTAL', $totalKekurangan, $this->formatMoney($total)];

        return $this->toExcel($content, $header, $footer, 'laporan-kekurangan-spp-' . $req->_kelas_jurusan_rombel);
    }
}
