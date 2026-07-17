<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function loginPin(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'pin' => 'required|digits:6',
        ]);

        $user = \App\Models\User::where('email', $request->login)
            ->orWhere('employee_id', $request->login)
            ->first();

        if (!$user || empty($user->pin) || !\Illuminate\Support\Facades\Hash::check($request->pin, $user->pin)) {
            return response()->json([
                'message' => 'PIN salah atau user tidak ditemukan',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load(['office', 'mutations', 'warnings', 'files', 'profile', 'employment']),
            'has_pin' => true,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required', // can be email or employee_id
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->login)
            ->orWhere('employee_id', $request->login)
            ->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email/NIP atau password salah',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load(['office', 'mutations', 'warnings', 'files', 'profile', 'employment']),
            'has_pin' => !empty($user->pin),
        ]);
    }

    public function setPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:6',
        ]);

        $user = $request->user();
        $user->pin = \Illuminate\Support\Facades\Hash::make($request->pin);
        $user->save();

        return response()->json([
            'message' => 'PIN berhasil diatur',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Berhasil keluar',
        ]);
    }

    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required',
        ]);

        if (\Illuminate\Support\Facades\Hash::check($request->pin, $request->user()->pin)) {
            return response()->json(['message' => 'PIN terverifikasi']);
        }

        return response()->json(['message' => 'PIN salah'], 401);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Password lama salah'], 422);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password berhasil diubah']);
    }

    public function changePin(Request $request)
    {
        $request->validate([
            'old_pin' => 'required',
            'new_pin' => 'required|digits:6|confirmed',
        ]);

        $user = $request->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->old_pin, $user->pin)) {
            return response()->json(['message' => 'PIN lama salah'], 422);
        }

        $user->pin = \Illuminate\Support\Facades\Hash::make($request->new_pin);
        $user->save();

        return response()->json(['message' => 'PIN berhasil diubah']);
    }
}
