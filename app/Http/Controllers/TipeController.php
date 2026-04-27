<?php

namespace App\Http\Controllers;

use App\Models\Tipe;
use Illuminate\Http\JsonResponse;

class TipeController extends Controller
{
    /**
     * GET /api/tipe-izin
     * List all permission/leave types for dropdown
     */
    public function index(): JsonResponse
    {
        $tipe = Tipe::orderBy('urut')->get();

        return response()->json([
            'data' => $tipe,
        ]);
    }
}
