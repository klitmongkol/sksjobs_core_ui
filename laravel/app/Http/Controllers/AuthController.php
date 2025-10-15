<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('name', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            if($token = Auth::guard('api')->attempt($credentials)){
                return redirect()->intended('/')->with([
                    'status' => 'เข้าสู่ระบบสำเร็จ',
                    'token' => $token,
                ]);
            }
        }
        return back()->withErrors([
            'name' => 'The provided credentials do not match our records.',
        ])->onlyInput('name');
    }
    public function register(Request $request){
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = Auth::login($user);

        return $this->respondWithToken($token);
    }

    public function me(){
        return response()->json(Auth::user());
    }

    public function logout(){
        Auth::logout();
        return redirect("/auth/login");
        //return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(){
        return $this->respondWithToken(Auth::refresh());
    }

    protected function respondWithToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }
}