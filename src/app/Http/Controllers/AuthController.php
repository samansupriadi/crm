<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    use HttpResponses;

    public function login(\App\Http\Requests\LoginUserRequest $request){
        $request->validated($request->all());

        if(!Auth::attempt($request->only(['email', 'password']))){
            return $this->error('', 'Credentials do not match', 401);
        }

        $user = User::where('email', $request->email)->first();

        return $this->success([
            'user'  => $user,
            'token' => $user->createToken('API Token of ' . $user->name)->plainTextToken,
        ]);
    }

    public function register(\App\Http\Requests\StoreUserRequest $request){
        $request->validated($request->all());

        $user = User::create([
            'name'  => $request->name,
            'email' => $request->email,
            'password'  => Hash::make($request->password)
        ]);

        return response()->json([
            'status'    => 200,
             'user'     => $request->name,
             'email'    => $request->email,
             'message'  => "Register Successfully",
             'token'    => $user->createToken('API Token of ' . $user->name)->plainTextToken, 
        ]);
        // return $this->success([
        //     'user'  => $user,
        //     'token' => $user->createToken('API Token of ' . $user->name)->plainTextToken, 
        // ]);
    }

    public function logout(){
        Auth::user()->currentAccessToken()->delete();

        return $this->success([
            'message'   => 'You Have successfully been logged out and your token has been deleted'
        ]);
    }

}
