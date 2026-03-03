<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;

class PegawaiController extends Controller
{
    /**
     * Fitur 1: Mengambil semua data pegawai (GET)
     * Digunakan Admin untuk melihat daftar seluruh pegawai se-Jateng.
     */
    public function index()
    {
        $dataPegawai = Pegawai::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Data pegawai berhasil diambil',
            'data' => $dataPegawai
        ]);
    }

    /**
     * Fitur 2: Menyimpan data pegawai baru (POST)
     * Digunakan Admin untuk mendaftarkan pegawai baru ke sistem Sinaga.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'email'    => 'required|email|unique:pegawai,email',
            'password' => 'required|min:6',
            'nip'      => 'required',
            'jabatan'  => 'nullable',
            'role'     => 'nullable'
        ]);

        $pegawaiBaru = Pegawai::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => bcrypt($request->password), // Password user absen diacak
            'nip'      => $request->nip,
            'jabatan'  => $request->jabatan,
            'role'     => $request->role,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pegawai berhasil ditambahkan!',
            'data'    => $pegawaiBaru
        ], 201);
    }

    /**
     * Fitur 3: Mengedit data pegawai (PUT/PATCH)
     * Digunakan Admin untuk mengubah data pegawai (misal pindah jabatan).
     */
    public function update(Request $request, $id)
    {
        // 1. Cari pegawainya berdasarkan user_id (Primary Key)
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pegawai tidak ditemukan'
            ], 404);
        }

        // 2. Validasi inputan
        $request->validate([
            'username' => 'required',
            'email'    => 'required|email|unique:pegawai,email,' . $id . ',user_id',
            'nip'      => 'required',
            'jabatan'  => 'nullable',
            'role'     => 'nullable'
        ]);

        // 3. Update datanya
        $pegawai->username = $request->username;
        $pegawai->email    = $request->email;
        $pegawai->nip      = $request->nip;
        $pegawai->jabatan  = $request->jabatan;
        $pegawai->role     = $request->role;

        // Update password jika admin mengisi kolom password baru
        if ($request->filled('password')) {
            $pegawai->password = bcrypt($request->password);
        }

        $pegawai->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data pegawai berhasil diupdate!',
            'data'    => $pegawai
        ]);
    }

    /**
     * Fitur 4: Menghapus data pegawai (DELETE)
     * Digunakan Admin untuk menghapus pegawai dari sistem.
     */
    public function destroy($id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pegawai tidak ditemukan'
            ], 404);
        }

        $pegawai->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data pegawai berhasil dihapus!'
        ]);
    }
}