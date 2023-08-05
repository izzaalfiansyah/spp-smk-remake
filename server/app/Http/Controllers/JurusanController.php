<?php

namespace App\Http\Controllers;

use App\Models\Jurusan as Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class JurusanController extends Controller
{
    public function index()
    {
        $builder = new Model();

        $data = $builder->orderBy('kode')->get();

        return response()->json($data);
    }

    public function kelas_jurusan_rombel()
    {
        $data = DB::table('siswa')->select('kelas', 'jurusan_kode', 'rombel')
            ->groupBy('kelas')->groupBy('jurusan_kode')->groupBy('rombel')->get();

        return $data;
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

    private function rules($id = null)
    {
        return [
            'kode' => ['required', $id ? Rule::unique('jurusan')->ignore($id, 'kode') : Rule::unique('jurusan')],
            'nama' => 'required',
            'kategori' => 'required|in:1,2',
            'jumlah_spp' => 'required|integer',
            'tabungan_wajib' => 'required|integer',
        ];
    }
}
