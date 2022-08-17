<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSpp as Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PembayaranSppController extends Controller
{
    public function index(Request $req)
    {
        $builder = new Model();

        if ($user_id = $req->_user_id) {
            $builder = $builder->where('user_id', $user_id);
        }

        if ($siswa_nisn = $req->_siswa_nisn) {
            $builder = $builder->where('siswa_nisn', $siswa_nisn);
        }

        if ($bulan = $req->_bulan) {
            $builder = $builder->where('bulan', $bulan);
        }

        $totalCount = $builder->count();

        if ($limit = $req->_limit) {
            $page = $req->_page;
            $builder = $builder->limit($limit)->skip(($page - 1) * $limit);
        }

        $data = $builder->orderBy('id', 'desc')->get();

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

    public function insertBatch(Request $req)
    {
        $schema = Validator::make($req->all(), [
            'data' => 'required',
            'data.*' => 'required',
            'data.*.siswa_nisn' => 'required',
            'data.*.user_id' => 'required',
            'data.*.bulan' => 'required',
            'data.*.jumlah_bayar' => 'required|integer',
            'data.*.tabungan_wajib' => 'required|integer',
            'data.*.status_kelas' => 'required|in:X,XI,XII',
        ]);

        if ($schema->fails()) {
            return response()->json($schema->errors()->all(), 400);
        }

        $totalData = 0;
        foreach ($req->data as $item) {
            Model::firstOrCreate([
                'siswa_nisn' => $item['siswa_nisn'],
                'status_kelas' => $item['status_kelas'],
                'bulan' => $item['bulan'],
            ], $item);

            $totalData += 1;
        }

        return response()->json("$totalData data berhasil disimpan");
    }

    private function rules()
    {
        return [
            'siswa_nisn' => 'required',
            'user_id' => 'required',
            'bulan' => 'required',
            'jumlah_bayar' => 'required|integer',
            'tabungan_wajib' => 'required|integer',
            'status_kelas' => 'required|in:X,XI,XII',
        ];
    }
}
