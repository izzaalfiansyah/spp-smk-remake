<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ptk as Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PtkController extends Controller
{
    public function index(Request $req)
    {
        $builder = new Model();

        if ($search = $req->q) {
            $builder = $builder->where(function ($query) use ($search) {
                $query->where('kode', 'like', "%$search%")
                    ->orWhere('nama', 'like', "%$search%")
                    ->orWhere('jabatan', 'like', "%$search%");
            });
        }

        $totalCount = $builder->count();

        if ($limit = $req->_limit) {
            $page = $req->_page ?: 1;
            $builder = $builder->limit($limit)->skip(($page - 1) * $limit);
        }

        $data = $builder->orderBy('kode')->get();
        return response()->json($data)->header('X-Total-Count', $totalCount);
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
            'kode' => ['required', $id ? Rule::unique('ptk')->ignore($id) : Rule::unique('ptk')],
            'nama' => 'required',
            'jabatan' => 'nullable',
        ];
    }

    public function laporan()
    {
        $builder = new Model();

        $data = $builder->get();

        foreach ($data as $key => $item) {
            $total_tabungan = (int) DB::table('tabungan_ptk')->select(DB::raw('sum(nominal) as total'))->where('ptk_id', $item->id)->first()?->total;

            $data[$key]->total_tabungan = $total_tabungan;
        }

        return response()->json($data)
            ->header('X-Print-Url', url('/laporan/ptk/print'))
            ->header('X-Excel-Url', url('/laporan/ptk/excel'));
    }

    public function laporan_print()
    {
        $data = $this->laporan()->original;

        $header = ['NO', 'KODE', 'NAMA', 'JABATAN', 'TOTAL TABUNGAN'];
        $content = [];
        $total = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->kode,
                $item->nama,
                $item->jabatan,
                $this->formatMoney($item->total_tabungan),
            ];

            $total += $item->total_tabungan;
        }

        $footer = ['', '', '', 'TOTAL', $this->formatMoney($total)];

        return $this->toPrint($content, $header, $footer);
    }

    public function laporan_excel()
    {
        $data = $this->laporan()->original;

        $header = ['NO', 'KODE', 'NAMA', 'JABATAN', 'TOTAL TABUNGAN'];
        $content = [];
        $total = 0;

        foreach ($data as $key => $item) {
            $content[] = [
                $key + 1,
                $item->kode,
                $item->nama,
                $item->jabatan,
                $this->formatMoney($item->total_tabungan),
            ];

            $total += $item->total_tabungan;
        }

        $footer = ['', '', '', 'TOTAL', $this->formatMoney($total)];

        return $this->toExcel($content, $header, $footer, 'laporan-tabungan-ptk');
    }
}
