<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'rfc' => 'required|string|max:255',
            ]);

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'rfc' => $request->rfc
            ]);

            // Generate the token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Return the response with the token
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'error' => 'Validation Error',
                'message' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            // Handle database errors
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while saving the user.',
            ], 500);
        } catch (\Exception $e) {
            // Handle other errors
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.',
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $user = Auth::user();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
