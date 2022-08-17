<?php

namespace App\Http\Controllers;

use App\Models\User as Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $builder = new Model();

        $data = $builder->orderBy('nama')->get();

        return response()->json($data);
    }

    public function show($id)
    {
        $item = Model::find($id);

        return response()->json($item);
    }

    public function store(Request $req)
    {
        $schema = Validator::make($req->all(), $this->rules());

        if ($schema->fails()) {
            return response()->json($schema->errors()->all(), 400);
        }

        $data = $schema->validated();
        $data['password'] = Hash::make($req->password);
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
        if ($req->password) {
            $data['password'] = Hash::make($req->password);
        } else {
            unset($data['password']);
        }
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

    public function login(Request $req)
    {
        $schema = Validator::make($req->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($schema->fails()) {
            return response()->json($schema->errors()->all(), 400);
        }

        $user = Model::where('username', $req->username)->get();

        if (count($user) > 0) {
            foreach ($user as $key => $item) {
                if (Hash::check($req->password, $item->password)) {
                    return response()->json($item);
                }
            }

            return response()->json(['password salah'], 400);
        } else {
            return response()->json(['username tidak ditemukan'], 400);
        }
    }

    private function rules($id = null)
    {
        return [
            'username' => ['required', $id ? Rule::unique('user')->ignore($id) : Rule::unique('user')],
            'password' => [Password::min(8), $id ? 'nullable' : 'required'],
            'nama' => 'required',
            'foto' => 'nullable',
            'role' => 'required|in:1,2',
        ];
    }
}
