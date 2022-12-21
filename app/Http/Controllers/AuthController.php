<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Password;
class AuthController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth:api', ['except' => ['login', 'register','forgot','passreset']]);
  }

  public function login(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }
    if (!$token = auth()->attempt($validator->validated())) {
      return response()->json(['errors' => ['Unauthorized']], 401);
    }

    return $this->createNewToken($token);
  }

  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|between:2,100',
        'lastname' => 'required|string|between:2,100',
        'phone' => 'string|required|unique:users',
        'email' => 'required|string|email|max:100|unique:users',
        'password' => 'required|string|confirmed|min:6',
    ]);

    if ($validator->fails()) {
      return response()->json(
          ['errors' => $validator->errors()]
           , 400);
    }
    $user = User::create(array_merge(
        $validator->validated(),
        ['password' => bcrypt($request->password)]
    ));
    $user->assignRole('owner');

    return response()->json([
        'message' => 'User successfully registered',
        'user' => $user,
        'roles' => $user->roles
    ], 201);
  }

  public function forgot(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',

    ]);
    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }


      $status = Password::sendResetLink(
          $request->only('email')
      );

      return  response()->json(['status' =>$status]);




  }

  public function userProfile()
  {
    return response()->json(auth()->user());
  }

  protected function createNewToken($token)
  {
    return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => auth()->factory()->getTTL() * 60,
        'user' => auth()->user()
    ]);

  }
  public function passreset(){

  }
}