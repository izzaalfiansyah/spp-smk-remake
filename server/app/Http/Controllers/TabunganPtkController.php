<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TabunganPtk as Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TabunganPtkController extends Controller
{
    public function index(Request $req)
    {
        $builder = new Model();

        if ($ptk_id = $req->_ptk_id) {
            $builder = $builder->where('ptk_id', $ptk_id);
        }

        if ($tanggal = $req->_tanggal) {
            $builder = $builder->whereDate('created_at', $tanggal);
        }

        $totalCount = $builder->count();

        if ($limit = $req->_limit) {
            $page = $req->_page ?: 1;
            $builder = $builder->limit($limit)->skip(($page - 1) * $limit);
        }

        $data = $builder->orderBy('created_at', 'desc')->get();
        return response()->json($data)->header('X-Total-Count', $totalCount)
            ->header('X-Excel-Url', url('/laporan/ptk/tabungan/excel?' . http_build_query($req->all())))
            ->header('X-Print-Url', url('/laporan/ptk/tabungan/print?' . http_build_query($req->all())));
    }

    public function store(Request $req)
    {
        $schema = Validator::make($req->all(), $this->rules());

        if ($schema->fails()) {
            return response()->json($schema->errors()->all(), 400);
        }

        $data = $schema->validated();
        $item = Model::create($data);

        return response()->json($item);
    }

    public function update(Request $req, $id)
    {
        $schema = Validator::make($req->all(), $this->rules($id));

        if ($schema->fails()) {
            return response()->json($schema->errors()->all(), 400);
        }

        $data = $schema->validated();
        $item = Model::find($id);
        if ($item) $item->update($data);

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = Model::find($id);
        if ($item) $item->delete();

        return response()->json($item);
    }

    public function rules($id = null)
    {
        return [
            'ptk_id' => 'required|integer',
            'user_id' => 'required|integer',
            'nominal' => 'required|integer',
        ];
    }

    public function laporan_print(Request $req)
    {
        $data = $this->index($req)->original;

        $header = ['NO', 'KODE', 'NAMA', 'JABATAN', 'ID', 'NOMINAL'];
        $content = [];
        $total = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->ptk?->kode,
                $item->ptk?->nama,
                $item->ptk?->jabatan,
                $item->nominal > 0 ? '1' : '2',
                $item->nominal,
            ];

            $total += $item->nominal;
        }

        $footer = ['', '', '', '', 'TOTAL', $this->formatMoney($total)];

        return $this->toPrint($content, $header, $footer);
    }

    public function laporan_excel(Request $req)
    {
        $data = $this->index($req)->original;

        $header = ['NO', 'KODE', 'NAMA', 'JABATAN', 'ID', 'NOMINAL'];
        $content = [];
        $total = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->ptk?->kode,
                $item->ptk?->nama,
                $item->ptk?->jabatan,
                $item->nominal > 0 ? '1' : '2',
                $item->nominal,
            ];

            $total += $item->nominal;
        }

        $footer = ['', '', '', '', 'TOTAL', $this->formatMoney($total)];

        return $this->toExcel($content, $header, $footer, 'laporan-tabungan-ptk');
    }
}
