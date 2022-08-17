<?php

namespace App\Http\Controllers;

use App\Models\BiayaLain as Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BiayaLainController extends Controller
{
    public function index(Request $req)
    {
        $builder = new Model();

        if ($jurusan_kode = $req->_jurusan_kode) {
            $builder = $builder->where('jurusan_data', 'like', '%"' . $jurusan_kode . '"%');
        }

        if ($kelas = $req->_kelas) {
            $builder = $builder->where('kelas_data', 'like', '%"' . $kelas . '"%');
        }

        $data = $builder->orderBy('created_at', 'desc')->get();

        return response()->json($data);
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
            'jenis' => 'required',
            'jumlah_bayar' => 'required|integer',
            'jurusan_data' => 'required',
            'jurusan_data.*' => 'required|string',
            'kelas_data' => 'required',
            'kelas_data.*' => 'required|in:X,XI,XII',
        ];
    }
}
