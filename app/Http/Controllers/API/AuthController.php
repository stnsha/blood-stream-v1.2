<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\APIAuthRequest;
use App\Models\Lab;
use App\Models\LabCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function username()
    {
        return 'username';
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'lab_id' => 'required|integer|exists:labs,id',
        ], [
            'lab_id.required' => 'Lab ID is required.',
            'lab_id.integer' => 'Invalid lab ID.',
            'lab_id.exists' => 'Lab ID does not exist.',
        ]);

        if ($validated) {
            $lab = Lab::findOrFail($validated['lab_id']);

            $existingCredential = LabCredential::where('lab_id', $lab->id)
                ->latest()
                ->first();

            if ($existingCredential) {
                $expiresAt = $existingCredential->created_at->timestamp + $existingCredential->expires_at;

                if (now()->timestamp < $expiresAt) {
                    return response()->json([
                        'data' => [
                            'message' => 'Lab session is still active. Please login.'
                        ]
                    ], 403);
                }
            }

            do {
                $username = $lab->code . '|' . strtoupper(strtolower(Str::random(3)));
            } while (LabCredential::where('username', $username)->exists());


            $plainPassword = Str::random(10);
            $expiresAt = Auth::guard('lab')->factory()->getTTL() * 60;

            $credential = LabCredential::create([
                'lab_id' => $lab->id,
                'username' => $username,
                'password' => bcrypt($plainPassword),
                'expires_at' => $expiresAt,
                'role' => 'lab',
                'is_active' => true,
            ]);

            $token = Auth::guard('lab')->login($credential);

            return response()->json([
                'message' => 'Lab credential successfully created.',
                'username' => $username,
                'password' => $plainPassword,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $expiresAt
            ]);
        }
    }

    public function login(APIAuthRequest $request)
    {
        $credentials = $request->only($this->username(), 'password');

        if (!$token = Auth::guard('lab')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('lab')->factory()->getTTL() * 60
        ]);
    }

    public function refresh(APIAuthRequest $request)
    {
        $credentials = $request->only($this->username(), 'password');

        $credential = LabCredential::where('username', $credentials['username'])->first();

        if (!$credential || !Hash::check($credentials['password'], $credential->password)) {
            return response()->json([
                'data' => [
                    'message' => 'Invalid username or password.'
                ]
            ], 401);
        }

        $expiresAt = $credential->created_at->timestamp + $credential->expires_at;

        if (now()->timestamp < $expiresAt) {
            $newToken = Auth::guard('lab')->login($credential);
            $updatedExpiresAt = Auth::guard('lab')->factory()->getTTL() * 60;

            return response()->json([
                'data' => [
                    'message' => 'Token refreshed successfully.',
                    'access_token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => $updatedExpiresAt
                ]
            ], 200);
        } else {
            return response()->json([
                'data' => [
                    'message' => 'Session expired. Please log in again.'
                ]
            ], 401);
        }
    }
}
