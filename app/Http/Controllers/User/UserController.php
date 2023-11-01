<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::all();

        //return response()->json(['Data' => $usuarios], 200);
        return $this->showAll($usuarios);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $reglas = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate($request, $reglas);

        $campos = $request->all();
        $campos['password'] = bcrypt($request->password);
        $campos['verified'] = User::VERIFIED;
        $campos['verification_token'] = User::generate_token_verification();
        $campos['admin'] = User::ADMINISTRATOR;

        $usuario = User::create($campos);

        return response()->json(['Data' => $usuario], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //Para encontrar un usuario con el id 
        //$usuario = User::find($id);

        // Busca un usuario con el id y en caso de no encontrarlo envia un error de no encontrado
        $usuario = User::findOrFail($id);

        return response()->json(['Data' => $usuario], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $usuario = User::findOrFail($id);

        $reglas = [
            'email' => 'unique:users,email,' . $usuario['id'],
            'password' => 'min:6|confirmed',
            'admin' => 'boolean',
        ];

        $this->validate($request, $reglas);

        if ($request->has('name')) {
            $usuario['name'] = $request['name'];
        }

        if ($request->has('email') && $usuario['email'] != $request['email']) {
            $usuario['verified'] = false; //Si el correo cambia, la verificacion es falsa y se genera nuevo token
            $usuario['verification_token'] = User::generate_token_verification();
            $usuario['email'] = $request['email'];
        }

        if ($request->has('password') && $usuario['password'] != $request['password']) {
            $usuario['password'] = bcrypt($request['password']);
        }

        if ($request->has('admin') && $request['admin'] == false) {
            if (!$usuario->is_verified()) {
                return response()->json(['error' => 'No tienes permisos para hacer esto.', 'code' => 409], 409);
            }
            $usuario['admin'] = $request['admin'];
        }

        if (!$usuario->isDirty()) {
            return response()->json(['error' => 'Debes cambiar al menos un valor.', 'code' => 422], 422);
        }

        $usuario->save();

        return response()->json(['Data' => $usuario], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $usuario = User::findOrFail($id);
        $usuario->delete();

        return response()->json(['Data' => $usuario], 200);
    }
}
