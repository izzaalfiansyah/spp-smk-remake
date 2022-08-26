<?php

namespace App\Http\Controllers;

use App\Models\Siswa as Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Shuchkin\SimpleXLSX;

class SiswaController extends Controller
{
    public function index(Request $req)
    {
        $builder = new Model();

        if ($search = $req->q) {
            $builder = $builder->where(function ($query) use ($search) {
                $query->where('nisn', 'like', "%$search%")
                    ->orWhere('nama', 'like', "%$search%");
            });
        }

        if ($jurusan_kode = $req->_jurusan_kode) {
            $builder = $builder->where('jurusan_kode', $jurusan_kode);
        }

        if ($kelas = $req->_kelas) {
            $builder = $builder->where('kelas', $kelas);
        }

        if ($rombel = $req->_rombel) {
            $builder = $builder->where('rombel', $rombel);
        }

        $totalCount = $builder->count();

        if ($limit = $req->_limit) {
            $page = $req->_page;
            $builder = $builder->limit($limit)->skip(($page - 1) * $limit);
        }

        $data = $builder->orderBy('nisn')->get();

        return response()->json($data)->header('X-Total-Count', $totalCount)->header('X-Import-File', url('/file/siswa-import.xlsx'));
    }

    public function show($id)
    {
        $item = Model::find($id);
        return response()->json($item);
    }

    public function showByNisn(Request $req)
    {
        $item = Model::where(['nisn' => $req->nisn])->first();
        return response()->json($item);
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

    public function updateByNisn(Request $req)
    {
        $schema = Validator::make($req->all(), $this->rules($req->old_nisn));

        if ($schema->fails()) {
            return response()->json($schema->errors()->all(), 400);
        }

        $data = $schema->validated();
        $item = Model::find($req->old_nisn);
        if ($item) $item->update($data);

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = Model::find($id);
        if ($item) $item->delete();

        return response()->json($item);
    }

    public function destroyByNisn(Request $req)
    {
        $item = Model::find($req->old_nisn);
        if ($item) $item->delete();

        return response()->json($item);
    }

    public function import(Request $req)
    {
        $schema = Validator::make($req->all(), [
            'file' => 'required',
        ]);

        if ($schema->fails()) {
            return response()->json($schema->errors()->all(), 400);
        }

        $file = $this->base64Upload($req->file, '/', '.xlsx');
        $total = 0;

        if ($sheet = SimpleXLSX::parse(public_path($file))) {
            foreach ($sheet->rows() as $key => $item) {
                if ($key > 0) {
                    $nisn = $item[0];
                    $nama = $item[1];
                    $kelas_jurusan_rombel = explode(' ', $item[2]);
                    $jurusan_kode = $kelas_jurusan_rombel[1];
                    $kelas = $kelas_jurusan_rombel[0];
                    $rombel = count($kelas_jurusan_rombel) < 3 ? '1' : $kelas_jurusan_rombel[2];

                    Model::firstOrCreate(['nisn' => $nisn], [
                        'nisn' => $nisn,
                        'nama' => $nama,
                        'jurusan_kode' => $jurusan_kode,
                        'kelas' => $kelas,
                        'rombel' => $rombel,
                    ]);

                    $total += 1;
                }
            }

            @unlink(public_path($file));
        } else {
            return SimpleXLSX::parseError();
        }

        return response()->json("$total data berhasil ditambahkan");
    }

    private function rules($id = null)
    {
        return [
            'nisn' => ['required', $id ? Rule::unique('siswa')->ignore($id, 'nisn') : Rule::unique('siswa')],
            'nama' => 'required',
            'jurusan_kode' => 'required',
            'kelas' => 'required|in:X,XI,XII,Alumni',
            'rombel' => 'required|integer',
            'diskon_spp' => 'nullable|integer',
            'diskon_biaya_lain' => 'nullable|integer',
        ];
    }
}
