<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogPresensi;

class LogPresensiController extends Controller
{
    /**
     * Menampilkan daftar log presensi.
     * Bisa dipanggil langsung atau dengan filter tanggal: /api/presensi?tanggal=2025-08-10
     */
    public function index(Request $request)
    {
        // 1. Mulai query dengan memanggil relasi 'pegawai' agar nama pegawai muncul
        $query = LogPresensi::with('pegawai');

        // 2. Jika ada kiriman tanggal dari Frontend, filter datanya
        if ($request->has('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }

        // 3. Ambil datanya
        $logs = $query->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data log presensi berhasil diambil',
            'data' => $logs
        ]);
    }
}