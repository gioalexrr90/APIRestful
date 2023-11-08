<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Exception;
use GuzzleHttp\Psr7\Response;
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
        } catch (ValidationException $e) {
            return $this->errorRespose($e->getMessage(), 400);
        }


        $campos = $request->all();
        $campos['password'] = bcrypt($request->password);
        $campos['verified'] = User::VERIFIED;
        $campos['verification_token'] = User::generate_token_verification();
        $campos['admin'] = User::ADMINISTRATOR;

        $user = User::create($campos);

        return $this->showOne($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //Para encontrar un usuario con el id
        //$usuario = User::find($id);

        // Busca un usuario con el id y en caso de no encontrarlo envia un error de no encontrado
        //$usuario = User::findOrFail($id);

        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {

        try {
            $reglas = [
                'email' => 'unique:users,email,' . $user['id'],
                'password' => 'min:6|confirmed',
                'admin' => 'boolean',
            ];

            $request->validate($reglas);
        } catch (ValidationException $e) {
            return $this->errorRespose($e->getMessage(), 500);
        }

        if ($request->has('name')) {
            $user['name'] = $request['name'];
        }

        if ($request->has('email') && $user['email'] != $request['email']) {
            $user['verified'] = false; //Si el correo cambia, la verificacion es falsa y se genera nuevo token
            $user['verification_token'] = User::generate_token_verification();
            $user['email'] = $request['email'];
        }

        if ($request->has('password') && $user['password'] != $request['password']) {
            $user['password'] = bcrypt($request['password']);
        }

        if ($request->has('admin') && $request['admin'] == false) {
            if (!$user->is_verified()) {
                return response()->json(['error' => 'No tienes permisos para hacer esto.', 'code' => 409], 409);
            }
            $user['admin'] = $request['admin'];
        }

        if (!$user->isDirty()) {
            return response()->json(['error' => 'Debes cambiar al menos un valor.', 'code' => 422], 422);
        }

        $user->save();

        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->showOne($user);
    }
}
