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

            $builder = $builder->select('siswa_nisn', 'created_at');

            $builder = $builder->whereDate('created_at', $tanggal);
            $builder = $builder->orderBy('created_at', 'desc');

            $user_id = '0';
            if ($user_id = $req->_user_id) {
                $builder = $builder->where('user_id', $user_id);
            }

            $data = $builder->get();

            $siswa_nisn = [];
            $items = [];

            foreach ($data as $key => $item) {
                if (!in_array($item->siswa_nisn, $siswa_nisn)) {
                    array_push($siswa_nisn, $item->siswa_nisn);

                    $builder_pembayaran_spp = DB::table('pembayaran_spp')->where('siswa_nisn', $item->siswa_nisn)->whereDate('created_at', $tanggal)->orderBy('created_at', 'desc');

                    if ($user_id) {
                        $builder_pembayaran_spp = $builder_pembayaran_spp->where('user_id', $user_id);
                    }

                    $pembayaran_spp = $builder_pembayaran_spp->get();

                    // return $pembayaran_spp;

                    $total_bulan = $total_bayar = $total_bulan = $total_tabungan = $total_uang_praktik = 0;

                    foreach ($pembayaran_spp as $spp) {
                        $total_bulan += 1;
                        $total_bayar += $spp->jumlah_bayar;
                        $total_tabungan += $spp->tabungan_wajib;
                        $total_uang_praktik += $spp->uang_praktik;
                        $pembayaran_user_id = $spp->user_id;
                        $waktu = date('H:i', strtotime($spp->created_at));
                    }

                    $item->total_bulan = $total_bulan . ' Bulan';
                    $item->total_bayar = (int) $total_bayar;
                    $item->total_tabungan = (int) $total_tabungan;
                    $item->total_uang_praktik = (int) $total_uang_praktik;
                    $item->siswa = DB::table('siswa')->where('nisn', $item->siswa_nisn)->first();
                    $item->user_id = $pembayaran_user_id;
                    $item->operator = DB::table('user')->where('id', $pembayaran_user_id)->first();
                    $item->waktu = $waktu;

                    array_push($items, $item);
                }
            }
        }

        return response()->json($items)
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
                $this->formatMoney($item->total_bayar + $item->total_tabungan + $item->total_uang_praktik),
            ];
            $total_bulan += (int) str_replace(' Bulan', '', $item->total_bulan);
            $total += $item->total_bayar + $item->total_tabungan + $item->total_uang_praktik;
        }

        return $this->toPrint($content, ['NO', 'NISN', 'NAMA SISWA', 'KELAS', 'BULAN', 'TOTAL'], ['', '', '', 'TOTAL', $total_bulan . ' Bulan', $this->formatMoney($total)], strtoupper("Laporan SPP tanggal " . formatDate($req->_tanggal)));
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
                $this->formatMoney($item->total_bayar + $item->total_tabungan + $item->total_uang_praktik),
            ];
            $total_bulan += (int) str_replace(' Bulan', '', $item->total_bulan);
            $total += $item->total_bayar + $item->total_tabungan + $item->total_uang_praktik;
        }

        return $this->toExcel($content, ['NO', 'NISN', 'NAMA SISWA', 'KELAS', 'BULAN', 'TOTAL'], ['', '', '', 'TOTAL', $total_bulan . ' Bulan', $this->formatMoney($total)], 'laporan-spp-' . $req->_tanggal);
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
                DB::raw('cast(sum(uang_praktik) as unsigned) as total_uang_praktik'),
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

            $pembayaranSpp = DB::table('pembayaran_spp')->whereDate('pembayaran_spp.created_at', '>=', $tanggal_awal)->whereDate('pembayaran_spp.created_at', '<=', $tanggal_akhir)->get();

            foreach ($pembayaranSpp as $key => $item) {
                $siswaNisns[] = $item->siswa_nisn;
            }

            $data = $builder->get();

            foreach ($data as $key => $item) {
                $jurusan = DB::table('jurusan')->where('kode', $item->jurusan_kode)->first();
                $siswaKeringanan = DB::table('siswa')
                    ->where('diskon_spp', '>', '0')
                    ->where('jurusan_kode', $item->jurusan_kode)
                    ->where('kelas', $item->kelas)
                    ->where('rombel', $item->rombel)
                    ->whereIn('nisn', $siswaNisns)
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
                $this->formatMoney($item->total_bayar + $item->total_tabungan + $item->total_uang_praktik),
            ];
            $total += $item->total_bayar + $item->total_tabungan + $item->total_uang_praktik;
        }

        return $this->toPrint($content, ['NO', 'KELAS', 'JUMLAH TOTAL', 'JUMLAH KERINGANAN', 'UANG KERINGANAN', 'TOTAL SPP KERINGANAN', 'TOTAL'], ['', '', '', '', '', 'TOTAL', $this->formatMoney($total)], strtoupper("Laporan SPP tanggal " . formatDate($req->_tanggal_awal) . ' SAMPAI ' . formatDate($req->_tanggal_akhir)));
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
                $this->formatMoney($item->total_bayar + $item->total_tabungan + $item->total_uang_praktik),
            ];
            $total += $item->total_bayar + $item->total_tabungan + $item->total_uang_praktik;
        }

        return $this->toExcel($content, ['NO', 'KELAS', 'JUMLAH TOTAL', 'JUMLAH KERINGANAN', 'UANG KERINGANAN', 'TOTAL SPP KERINGANAN', 'TOTAL'], ['', '', '', '', '', 'TOTAL', $this->formatMoney($total)], 'laporan-spp-' . $req->_tanggal_awal . '-sampai-' . $req->_tanggal_akhir);
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
                        DB::raw('cast(sum(uang_praktik) as unsigned) as total_uang_praktik'),
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
                $this->formatMoney($item->spp->total_bayar + $item->spp->total_tabungan + $item->spp->total_uang_praktik),
            ];
            $total_jumlah += $item->spp->jumlah_pembayaran;
            $total += $item->spp->total_bayar + $item->spp->total_tabungan + $item->spp->total_uang_praktik;
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
                $this->formatMoney($item->spp->total_bayar + $item->spp->total_tabungan + $item->spp->total_uang_praktik),
            ];
            $total_jumlah += $item->spp->jumlah_pembayaran;
            $total += $item->spp->total_bayar + $item->spp->total_tabungan + $item->spp->total_uang_praktik;
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
