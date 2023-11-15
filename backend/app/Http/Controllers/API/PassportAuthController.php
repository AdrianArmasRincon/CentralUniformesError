<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
// use App\Models\Role;
use Validator;

class PassportAuthController extends Controller
{
    public function login(Request $request)
    {
        $input = $request->all();

        Auth::attempt($input);

        $user = Auth::user();
        
        $token = $user->createToken('Laravel10PassportAuth')->accessToken;
        return Response(['status' => 200,'token' => $token],200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|min:4',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Autenticar al nuevo usuario
        Auth::login($user);

        // Generar token de acceso
        $token = $user->createToken('Laravel10PassportAuth')->accessToken;

        // Devolver la respuesta con el token
        return response(['status' => 200, 'token' => $token], 200);
    }

    public function getUserDetail()
    {  
        if(Auth::guard('api')->check()){
            $user = Auth::guard('api')->user();
            return Response(['data' => $user],200);
        }
        return Response(['data' => 'Unauthorized'],401);
    }

    public function userLogout()
    {
        if(Auth::guard('api')->check()){
            $accessToken = Auth::guard('api')->user()->token();
                \DB::table('oauth_refresh_tokens')
                    ->where('access_token_id', $accessToken->id)
                    ->update(['revoked' => true]);
            $accessToken->revoke();

            return Response(['data' => 'Unauthorized','message' => 'User logout successfully'],200);
        } 
        return Response(['data' => 'Unauthorized'],401);
    }

    public function index(){
        $users = User::all();
        return response()->json([
            "success" => true,
            "message" => "User List",
            "data" => $users
        ]);
    }

    public function show($id){
        $users = User::find($id);
        if (is_null($users)) {
            return $this->sendError('User not found.');
        }
        return response()->json([
            "success" => true,
            "message" => "User retrieved successfully.",
            "data" => $users
        ]);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
        'name' => 'required',
        'email' => 'required',
        'password' => 'required'
        ]);
        if ($validator->fails()) {
        return $this->sendError('Validation Error.', $validator->errors());
        }
        $users->name = $input['name'];
        $users->email = $input['email'];
        $users->password = $input['password'];
        // $product->save();
        $users->save();
        return response()->json([
            "success" => true,
            "message" => "User updated successfully.",
            "data" => $users
        ]);
    }

    public function destroy($id)
    {
        $users->delete();
        return response()->json([
            "success" => true,
            "message" => "User deleted successfully.",
            "data" => $users
        ]);
    }
/*
    public function roles($id){
        $user = User::find($id);

        $userRoles = [];
        
        foreach ($user->roles as $role) {
            $userRoles[] = [
                'name' => $role->name,
            ];
        }

        //devolver los roles como JSON
        return response()->json(['user_roles' => $userRoles]);
    }


    public function assignRoles(Request $request, $id)
    {
        // Validar la solicitud
        $request->validate([
            'role_ids' => 'required|array',
        ]);

        // Encontrar el usuario por su ID
        $user = User::find($id);

        // Asignar roles al usuario
        foreach ($request->input('role_ids') as $role_id) {
            $role = Role::find($role_id);
            
            if ($role) {
                $user->roles()->attach($role->id);
            }
        }
        return response()->json([
            'message' => 'Roles asignados correctamente'
        ]);
    }

    public function modifyRoles(Request $request, $id)
    {
        // Validar la solicitud según tus necesidades
        $request->validate([
            'role_ids' => 'required|array',
        ]);

        // Encontrar el usuario por su ID
        $user = User::find($id);

        // Modificar roles del usuario
        $roleIds = $request->input('role_ids');

        // Opción 1: sync (reemplazar roles existentes con los proporcionados)
        $user->roles()->sync($roleIds);

        // Opción 2: syncWithoutDetaching (agregar nuevos roles sin eliminar los existentes)
        // $user->roles()->syncWithoutDetaching($roleIds);

        // Puedes devolver una respuesta JSON
        return response()->json(['message' => 'Roles modificados correctamente']);
    }

    public function deleteRole(Request $request, $id, $roleId)
    {
        // Validar la solicitud según tus necesidades
        // ...

        // Encontrar el usuario por su ID
        $user = User::find($id);

        // Encontrar el rol por su ID
        $role = Role::find($roleId);

        // Verificar si el usuario y el rol existen
        if ($user && $role) {
            // Eliminar el rol del usuario
            $user->roles()->detach($role->id);

            // Puedes devolver una respuesta JSON
            return response()->json(['message' => 'Rol eliminado correctamente']);
        } else {
            // Puedes devolver una respuesta JSON indicando que el usuario o el rol no fueron encontrados
            return response()->json(['message' => 'Usuario o rol no encontrados'], 404);
        }
    }
    */
}