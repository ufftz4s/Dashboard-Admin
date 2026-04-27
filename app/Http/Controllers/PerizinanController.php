<?php

namespace App\Http\Controllers;

use App\Models\Perizinan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerizinanController extends Controller
{
    /**
     * GET /api/perizinan
     * List all permission requests with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Perizinan::with(['pegawai', 'tipePerizinan']);

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
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

        // Filter by tipe
        if ($tipe = $request->input('tipe')) {
            $query->where('tipe', $tipe);
        }

        $perPage = $request->input('per_page', 10);
        $perizinan = $query->orderBy('create_datetime', 'desc')
                           ->orderBy('id', 'desc')
                           ->paginate($perPage);

        return response()->json([
            'data' => $perizinan->items(),
            'meta' => [
                'current_page' => $perizinan->currentPage(),
                'last_page' => $perizinan->lastPage(),
                'per_page' => $perizinan->perPage(),
                'total' => $perizinan->total(),
            ],
        ]);
    }

    /**
     * POST /api/perizinan
     * Create a new permission request
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nip' => 'required|exists:pegawai,nip',
            'tipe' => 'required|exists:tipe,id',
            'alasan' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lampiran' => 'nullable|string',
            'no_surat' => 'nullable|string',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai);
        $tanggalSelesai = Carbon::parse($request->tanggal_selesai);
        $jumlahHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

        $perizinan = Perizinan::create([
            'nip' => $request->nip,
            'tipe' => $request->tipe,
            'alasan' => $request->alasan,
            'lampiran' => $request->lampiran,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_hari' => $jumlahHari,
            'tanggal_pengajuan' => Carbon::today(),
            'status' => 'pending',
            'create_datetime' => Carbon::now(),
            'no_surat' => $request->no_surat,
        ]);

        $perizinan->load(['pegawai', 'tipePerizinan']);

        return response()->json([
            'message' => 'Permission request created successfully',
            'data' => $perizinan,
        ], 201);
    }

    /**
     * GET /api/perizinan/{id}
     * Show detail of a permission request
     */
    public function show($id): JsonResponse
    {
        $perizinan = Perizinan::with(['pegawai', 'tipePerizinan'])->find($id);

        if (!$perizinan) {
            return response()->json(['message' => 'Permission request not found'], 404);
        }

        return response()->json(['data' => $perizinan]);
    }

    /**
     * PUT /api/perizinan/{id}
     * Update a permission request
     */
    public function update(Request $request, $id): JsonResponse
    {
        $perizinan = Perizinan::find($id);

        if (!$perizinan) {
            return response()->json(['message' => 'Permission request not found'], 404);
        }

        $request->validate([
            'nip' => 'sometimes|exists:pegawai,nip',
            'tipe' => 'sometimes|exists:tipe,id',
            'alasan' => 'sometimes|string',
            'tanggal_mulai' => 'sometimes|date',
            'tanggal_selesai' => 'sometimes|date|after_or_equal:tanggal_mulai',
            'lampiran' => 'nullable|string',
            'no_surat' => 'nullable|string',
        ]);

        $data = $request->only(['nip', 'tipe', 'alasan', 'lampiran', 'tanggal_mulai', 'tanggal_selesai', 'no_surat']);

        if (isset($data['tanggal_mulai']) && isset($data['tanggal_selesai'])) {
            $data['jumlah_hari'] = Carbon::parse($data['tanggal_mulai'])->diffInDays(Carbon::parse($data['tanggal_selesai'])) + 1;
        }

        $data['update_datetime'] = Carbon::now();

        $perizinan->update($data);
        $perizinan->load(['pegawai', 'tipePerizinan']);

        return response()->json([
            'message' => 'Permission request updated successfully',
            'data' => $perizinan,
        ]);
    }

    /**
     * PATCH /api/perizinan/{id}/approve
     * Approve a permission request
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $perizinan = Perizinan::find($id);

        if (!$perizinan) {
            return response()->json(['message' => 'Permission request not found'], 404);
        }

        $request->validate([
            'disetujui_oleh' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        $perizinan->update([
            'status' => 'approved',
            'tanggal_persetujuan' => Carbon::today(),
            'disetujui_oleh' => $request->disetujui_oleh,
            'catatan' => $request->catatan,
            'update_datetime' => Carbon::now(),
        ]);

        $perizinan->load(['pegawai', 'tipePerizinan']);

        return response()->json([
            'message' => 'Permission request approved successfully',
            'data' => $perizinan,
        ]);
    }

    /**
     * PATCH /api/perizinan/{id}/reject
     * Reject a permission request
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $perizinan = Perizinan::find($id);

        if (!$perizinan) {
            return response()->json(['message' => 'Permission request not found'], 404);
        }

        $request->validate([
            'catatan' => 'required|string',
        ]);

        $perizinan->update([
            'status' => 'rejected',
            'catatan' => $request->catatan,
            'update_datetime' => Carbon::now(),
        ]);

        $perizinan->load(['pegawai', 'tipePerizinan']);

        return response()->json([
            'message' => 'Permission request rejected',
            'data' => $perizinan,
        ]);
    }
}
