<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\roles;
use Validator;

use Illuminate\Support\Facades\Log;
use \Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class PassportAuthController extends Controller
{
    public function sendResponse($result, $message)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

    private function extractBasicAuthData(Request $request)
    {
        $header = $request->header('Authorization');
        if(!Str::startsWith($header, 'Basic ')){
            return null;
        }

        $encoded = explode('Basic ', $header);
        $decoded = base64_decode($encoded[1]);
        list($email, $password) = explode(":", $decoded);

        $dataDecoded = $request->all(); //only name should be there
        $dataDecoded['email'] = $email;
        $dataDecoded['password'] = $password;

        return $dataDecoded;
    }

    public function login(Request $request): JsonResponse
    {
        $dataDecoded = $this->extractBasicAuthData($request);
        if($dataDecoded == null) return $this->sendError('Not valid Basic Auth');

        if(Auth::attempt(['email' => $dataDecoded['email'], 'password' => $dataDecoded['password']])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')-> accessToken; 
            $success['name'] =  $user->name;
            $success['idUser'] = $user->id;
   
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }

    public function register(Request $request)
    {
        $dataDecoded = $this->extractBasicAuthData($request);

        if($dataDecoded == null) return $this->sendError('Not valid Basic Auth');

        $validator = Validator::make($dataDecoded, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);
     
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
     
        $input = $dataDecoded;
        $input['password'] = bcrypt($input['password']);

        try {
            $user = User::create($input);
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['name'] =  $user->name;
            return $this->sendResponse($success, 'User register successfully.');
        } catch (\Illuminate\Database\QueryException $exception) {
            return $this->sendError('User Register Error.', $exception->errorInfo);
        }
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
        $users = User::find($id);
        if ($input['name']){
            $users->name = $input['name'];
        }
        if ($input['email']){
            $users->email = $input['email'];
        }
        if ($input['password']){
            $users->password = $input['password'] = bcrypt($input['password']);
        }
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
        $user = User::find($id);
        if ($user) {
            $user->delete();
        }
        return response()->json([
            "success" => true,
            "message" => "User deleted successfully.",
        ]);
    }

    public function addRole($userId,$roleId)
    {
        // Find the user by ID
        $user = User::find($userId);

        // Find the role by ID
        $role = roles::find($roleId);

        // Check if the user and role are found
        if ($user === null) {
            return response()->json([
                "success" => false,
                "message" => "User not found.",
                "message" => $userId,
            ]);
        }

        if ($role === null) {
            return response()->json([
                "success" => false,
                "message" => "Rol not found.",
                "message" => $roleId,
        ]);
    }

        // Attach the role to the user
        $user->roles()->attach($role);

        return response()->json([
            "success" => true,
            "message" => "Rol added correctly."
        ]);
    }

    public function removeRole($userId, $roleId)
    {
        $user = User::find($userId);
        $role = roles::find($roleId);

        // Check if the user and role are found
        if ($user === null) {
            return response()->json([
                "success" => false,
                "message" => "User not found.",
                "message" => $userId,
            ]);
        }

        if ($role === null) {
            return response()->json([
                "success" => false,
                "message" => "Roe not found."
        ]);
        }
        $user->roles()->detach($role);

        return response()->json([
            "success" => true,
            "message" => "Rol removed correctly"
        ]);
        
    }

    public function showRoles($userId)
    {
        $user = User::find($userId);
        $roles = $user->roles;

        // Check if the user and role are found
        if ($user === null) {
            return response()->json([
                "success" => false,
                "message" => "User not found.",
                "message" => $userId,
            ]);
        }

        return response()->json([
            "success" => true,
            "message" => "User roles correctly",
            "data" => $roles
        ]);
    }
}