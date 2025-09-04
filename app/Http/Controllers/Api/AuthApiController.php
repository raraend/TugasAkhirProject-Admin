<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        // Validasi data login
        $credentials = $request->validate([
            'email' => ['required', 'email'],  // Email wajib dengan format benar
            'password' => ['required'],        // Password wajib diisi
        ]);

        // Jika autentikasi gagal (email/password salah)
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ambil data user yang berhasil login
        $user = Auth::user();

        // Hapus semua token lama user → supaya token sebelumnya tidak bisa dipakai
        $user->tokens()->delete();

        // Buat token baru menggunakan Sanctum
        // Nama "web2-token" hanya sebagai label aja
        $token = $user->createToken('web2-token')->plainTextToken;

        // Response ke client dengan token + data user
        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang aktif dipakai oleh user
        $request->user()->currentAccessToken()->delete();

        // Response sukses logout
        return response()->json(['message' => 'Logged out']);
    }
}
