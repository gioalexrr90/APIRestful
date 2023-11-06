<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

        try {
            $request->validate($reglas);
        } catch (Exception $e) {
            return $this->errorRespose($e->getMessage(), 500);
        }


        $campos = $request->all();
        $campos['password'] = bcrypt($request->password);
        $campos['verified'] = User::VERIFIED;
        $campos['verification_token'] = User::generate_token_verification();
        $campos['admin'] = User::ADMINISTRATOR;

        $usuario = User::create($campos);

        return $this->showOne($usuario, 201);
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

        return $this->showOne($usuario);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $usuario = User::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $model = strtolower(class_basename($e->getModel()));
            return $this->errorRespose('Modelo o instancial ' . $model . ' no', 404);
        }

        try {
            $reglas = [
                'email' => 'unique:users,email,' . $usuario['id'],
                'password' => 'min:6|confirmed',
                'admin' => 'boolean',
            ];

            $request->validate($reglas);
        } catch (ValidationException $e) {
            return $this->errorRespose($e->getMessage(), 500);
        }

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

        return $this->showOne($usuario);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $usuario = User::findOrFail($id);
        $usuario->delete();

        return $this->showOne($usuario);
    }
}
