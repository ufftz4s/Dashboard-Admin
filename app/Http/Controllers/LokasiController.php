<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    /**
     * Menampilkan daftar lokasi presensi (dengan search & pagination).
     */
    public function index(Request $request)
    {
        $query = Lokasi::query();

        // Search by instansi or unit_instansi
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('instansi', 'ILIKE', "%{$search}%")
                  ->orWhere('unit_instansi', 'ILIKE', "%{$search}%")
                  ->orWhere('kode_lokasi', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $lokasi = $query->orderBy('kode_lokasi', 'asc')->paginate($perPage);

        return response()->json([
            'data' => $lokasi->items(),
            'meta' => [
                'current_page' => $lokasi->currentPage(),
                'last_page' => $lokasi->lastPage(),
                'per_page' => $lokasi->perPage(),
                'total' => $lokasi->total(),
            ],
        ]);
    }

    /**
     * Menyimpan lokasi presensi baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_lokasi' => ['required', 'integer', 'unique:lokasi,kode_lokasi'],
            'instansi' => ['required', 'string', 'max:255'],
            'unit_instansi' => ['required', 'string', 'max:255'],
            'longitude' => ['required', 'numeric'],
            'latitude' => ['required', 'numeric'],
        ]);

        $lokasi = Lokasi::create($validated);

        return response()->json([
            'message' => 'Lokasi presensi berhasil ditambahkan.',
            'data' => $lokasi,
        ], 201);
    }

    /**
     * Mengupdate lokasi presensi.
     */
    public function update(Request $request, $kodeLokasi)
    {
        $lokasi = Lokasi::where('kode_lokasi', $kodeLokasi)->firstOrFail();

        $validated = $request->validate([
            'instansi' => ['required', 'string', 'max:255'],
            'unit_instansi' => ['required', 'string', 'max:255'],
            'longitude' => ['required', 'numeric'],
            'latitude' => ['required', 'numeric'],
        ]);

        $lokasi->update($validated);

        return response()->json([
            'message' => 'Lokasi presensi berhasil diperbarui.',
            'data' => $lokasi,
        ]);
    }

    /**
     * Menghapus lokasi presensi.
     */
    public function destroy($kodeLokasi)
    {
        $lokasi = Lokasi::where('kode_lokasi', $kodeLokasi)->firstOrFail();
        $lokasi->delete();

        return response()->json([
            'message' => 'Lokasi presensi berhasil dihapus.',
        ]);
    }
}
