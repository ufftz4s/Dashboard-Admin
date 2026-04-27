<?php

namespace App\Http\Controllers;

use App\Models\JadwalKerja;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JadwalKerjaController extends Controller
{
    /**
     * GET /api/jadwal-kerja
     * List work schedules with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = JadwalKerja::with('pegawai');

        // Filter by NIP
        if ($nip = $request->input('nip')) {
            $query->where('nip', $nip);
        }

        // Filter by date range
        if ($from = $request->input('from')) {
            $query->where('tanggal', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('tanggal', '<=', $to);
        }

        // Search by NIP or pegawai name
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'ilike', "%{$search}%")
                  ->orWhereHas('pegawai', function ($pq) use ($search) {
                      $pq->where('username', 'ilike', "%{$search}%");
                  });
            });
        }

        $perPage = $request->input('per_page', 15);
        $jadwal = $query->orderBy('tanggal', 'desc')
                        ->orderBy('id', 'desc')
                        ->paginate($perPage);

        return response()->json([
            'data' => $jadwal->items(),
            'meta' => [
                'current_page' => $jadwal->currentPage(),
                'last_page' => $jadwal->lastPage(),
                'per_page' => $jadwal->perPage(),
                'total' => $jadwal->total(),
            ],
        ]);
    }

    /**
     * POST /api/jadwal-kerja
     * Create a new work schedule
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nip' => 'required|exists:pegawai,nip',
            'tanggal' => 'required|date',
            'jam_masuk' => 'required',
            'jam_plng' => 'required',
        ]);

        $jadwal = JadwalKerja::create([
            'nip' => $request->nip,
            'tanggal' => $request->tanggal,
            'jam_masuk' => $request->jam_masuk,
            'jam_plng' => $request->jam_plng,
            'dibuat_oleh' => $request->input('dibuat_oleh', 'Admin'),
            'dibuat_pada' => Carbon::now(),
        ]);

        $jadwal->load('pegawai');

        return response()->json([
            'message' => 'Work schedule created successfully',
            'data' => $jadwal,
        ], 201);
    }

    /**
     * PUT /api/jadwal-kerja/{id}
     * Update a work schedule
     */
    public function update(Request $request, $id): JsonResponse
    {
        $jadwal = JadwalKerja::find($id);

        if (!$jadwal) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        $request->validate([
            'nip' => 'sometimes|exists:pegawai,nip',
            'tanggal' => 'sometimes|date',
            'jam_masuk' => 'sometimes',
            'jam_plng' => 'sometimes',
        ]);

        $jadwal->update($request->only(['nip', 'tanggal', 'jam_masuk', 'jam_plng']));
        $jadwal->load('pegawai');

        return response()->json([
            'message' => 'Work schedule updated successfully',
            'data' => $jadwal,
        ]);
    }

    /**
     * DELETE /api/jadwal-kerja/{id}
     * Delete a work schedule
     */
    public function destroy($id): JsonResponse
    {
        $jadwal = JadwalKerja::find($id);

        if (!$jadwal) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        $jadwal->delete();

        return response()->json([
            'message' => 'Work schedule deleted successfully',
        ]);
    }
}
