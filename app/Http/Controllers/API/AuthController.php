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
                return response()->json([
                    'data' => [
                        'message' => 'Lab credential already exists. Please login.'
                    ]
                ], 409);
            }

            do {
                $username = $lab->code . $lab->id . strtoupper(strtolower(Str::random(4)));
            } while (LabCredential::where('username', $username)->exists());


            $plainPassword = Str::random(15);

            LabCredential::create([
                'lab_id' => $lab->id,
                'username' => $username,
                'password' => bcrypt($plainPassword),
                'role' => 'lab',
                'is_active' => true,
            ]);

            return response()->json([
                'message' => 'Lab credential successfully created.',
                'username' => $username,
                'password' => $plainPassword
            ]);
        }
    }

    public function login(APIAuthRequest $request)
    {
        $credentials = $request->only($this->username(), 'password');

        if (!$token = Auth::guard('lab')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $expiresIn = Auth::guard('lab')->factory()->getTTL() * 60;

        $labCredential = LabCredential::where('username', $credentials['username'])->first();

        if ($labCredential) {
            $labCredential->expires_at = $expiresIn;
            $labCredential->save();
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiresIn
        ]);
    }

    public function logout()
    {
        try {
            Auth::guard('lab')->logout();

            return response()->json([
                'message' => 'Successfully logged out.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to logout, please try again.'
            ], 500);
        }
    }
}
