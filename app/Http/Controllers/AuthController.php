<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request; // Pakai huruf R besar biar PHP gak ngambek
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // validasi input dari flutter
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // cari user di database
        $user = User::where('username', $request->username)->first();

        // checking apakah user ada dan password benar
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Balasan jika gagal
            return response()->json([
                'success' => false,
                'message' => 'Username atau Password salah!'
            ], 401);
        }

        // --- TAMBAHAN BARU: CEK STATUS AKTIF/NONAKTIF ---
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun dinonaktifkan! Silakan hubungi Owner/Admin.'
            ], 403); // 403 Forbidden
        }
        // ------------------------------------------------

        // jika sukses hapus token lama dan buat token baru
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        // kirim balasan ke flutter
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil, ' . $user->username . '!',
            'token' => $token,
            'role' => $user->role,
            'data' => $user
        ], 200);
    }

    public function logout(Request $request)
    {
        // hapus token yang sedang aktif
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Logout'
        ], 200);
    }
}
