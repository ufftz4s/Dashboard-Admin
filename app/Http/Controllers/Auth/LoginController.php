<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Cari Admin di tabel 'users'
        $admin = User::where('email', $request->email)->first();

        // 3. Cek Password
        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Admin tidak ditemukan atau password salah.',
            ], 401);
        }

        // 4. Buat Token untuk Admin
        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login Admin Berhasil!',
            'data' => [
                'admin' => $admin,
                'token' => $token
            ]
        ]);
    }
}