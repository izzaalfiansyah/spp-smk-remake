<?php

namespace App\Http\Controllers;

use App\Models\PembayaranBiayaLain as Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PembayaranBiayaLainController extends Controller
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

        if ($biaya_lain_id = $req->_biaya_lain_id) {
            $builder = $builder->where('biaya_lain_id', $biaya_lain_id);
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

    private function rules()
    {
        return [
            'siswa_nisn' => 'required',
            'user_id' => 'required|integer',
            'biaya_lain_id' => 'required|integer',
            'jumlah_bayar' => 'required|integer',
        ];
    }
}
