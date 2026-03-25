<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardStatsController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $today = Carbon::today()->toDateString();
            $onTimeLimit = '08:00:00';

            $totalPegawai = (int) DB::table('pegawai')
                ->whereNotNull('nip')
                ->distinct()
                ->count('nip');

            $todayAttendance = DB::table('log_presensi')
                ->whereNotNull('nip')
                ->where(function ($query) {
                    $query->whereNull('is_validate')
                        ->orWhere('is_validate', true);
                })
                ->whereRaw('DATE(COALESCE(tanggal, created_at)) = ?', [$today])
                ->groupBy('nip')
                ->selectRaw("
                    nip,
                    MIN(
                        CASE
                            WHEN NULLIF(BTRIM(COALESCE(waktu, '')), '') ~ '^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$'
                                THEN CAST(NULLIF(BTRIM(waktu), '') AS time)
                            WHEN created_at IS NOT NULL
                                THEN CAST(created_at AS time)
                            ELSE NULL
                        END
                    ) AS first_check_in_time
                ");

            $hadirHariIni = (int) DB::query()
                ->fromSub($todayAttendance, 'attendance')
                ->count();

            $tepatWaktu = (int) DB::query()
                ->fromSub($todayAttendance, 'attendance')
                ->whereNotNull('first_check_in_time')
                ->where('first_check_in_time', '<=', $onTimeLimit)
                ->count();

            $terlambat = (int) DB::query()
                ->fromSub($todayAttendance, 'attendance')
                ->whereNotNull('first_check_in_time')
                ->where('first_check_in_time', '>', $onTimeLimit)
                ->count();

            $izinHariIni = (int) DB::table('perizinan')
                ->whereNotNull('nip')
                ->whereDate('tanggal_mulai', '<=', $today)
                ->whereDate('tanggal_selesai', '>=', $today)
                ->where(function ($query) {
                    $query->whereIn(DB::raw("LOWER(COALESCE(status, ''))"), [
                        '1',
                        'approved',
                        'approve',
                        'disetujui',
                    ])->orWhere(function ($subQuery) {
                        $subQuery->whereNotNull('tanggal_persetujuan')
                            ->whereNotNull('disetujui_oleh');
                    });
                })
                ->distinct()
                ->count('nip');

            return response()->json([
                'totalPegawai' => $totalPegawai,
                'hadirHariIni' => $hadirHariIni,
                'tepatWaktu' => $tepatWaktu,
                'izinHariIni' => $izinHariIni,
                'terlambat' => $terlambat,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to load dashboard stats.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }
}
