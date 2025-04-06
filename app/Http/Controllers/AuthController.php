<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use Illuminate\Http\Response;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request) {
        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
    
            return response()->json([
                'message' => 'Sucessfully registerd',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->validator->errors()->first(), // Get first error message
            ], 422);
        }

    }

    public function login(Request $request)
    {
        try{
            $credentials = $request->all();
    
            if (!$token = Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }
    
            // create the token
            $result = $this->respondWithToken($token);
    
            // Set cookie with 1-hour expiration
            $cookie = cookie('jwt_token', $token, 60); // 60 minutes (1 hour)

            $user = Auth::user();
            
            $user->image = ($user->image) ? asset('storage/' . $user->image) : null;
    
            return response()->json([
                'message' => 'success',
                'data' => $result,
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout() {
        try {
            // Invalidate the user's token
            Auth::logout();

            // Forget the cookie
            return response()->json([
                'message' => 'Logged out successfully'
            ], 200)->cookie(Cookie::forget('jwt_token'));

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'access_type' => 'Bearer',
            'expires_at' => Auth::factory()->getTTL() * 60
        ];
    }

    public function verifyAuth()
    {
        return response()->json([
            'message' => 'Success',
        ], 200);
        
    }
}
