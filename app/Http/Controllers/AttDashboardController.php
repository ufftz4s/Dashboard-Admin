<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AttDashboardController extends Controller
{
    /**
     * GET /api/dashboard/stats
     * Total employees, present today, absent today, late today
     */
    public function stats(): JsonResponse
    {
        $today = Carbon::today();
        $totalEmployees = Employee::count();

        $presentToday = Attendance::where('date', $today)
            ->where('status', 'present')
            ->count();

        $absentToday = Attendance::where('date', $today)
            ->where('status', 'absent')
            ->count();

        $lateToday = Attendance::where('date', $today)
            ->where('status', 'late')
            ->count();

        return response()->json([
            'data' => [
                'total_employees' => $totalEmployees,
                'present_today' => $presentToday,
                'absent_today' => $absentToday,
                'late_today' => $lateToday,
            ]
        ]);
    }

    /**
     * GET /api/dashboard/weekly-attendance
     * Attendance data for the last 7 days (present & absent per day)
     */
    public function weeklyAttendance(): JsonResponse
    {
        $days = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayName = $date->format('D');

            $present = Attendance::where('date', $date)
                ->whereIn('status', ['present', 'late'])
                ->count();

            $absent = Attendance::where('date', $date)
                ->where('status', 'absent')
                ->count();

            $days->push([
                'day' => $dayName,
                'date' => $date->format('Y-m-d'),
                'present' => $present,
                'absent' => $absent,
            ]);
        }

        return response()->json([
            'data' => $days,
        ]);
    }

    /**
     * GET /api/dashboard/attendance-rates
     * Weekly rate (%) and monthly rate (%)
     */
    public function attendanceRates(): JsonResponse
    {
        $totalEmployees = Employee::count();

        if ($totalEmployees === 0) {
            return response()->json([
                'data' => [
                    'weekly_rate' => 0,
                    'monthly_rate' => 0,
                ]
            ]);
        }

        // Weekly rate: last 7 days
        $weekStart = Carbon::today()->subDays(6);
        $weekEnd = Carbon::today();

        $weeklyPresent = Attendance::whereBetween('date', [$weekStart, $weekEnd])
            ->whereIn('status', ['present', 'late'])
            ->count();

        $weeklyTotal = $totalEmployees * 7;
        $weeklyRate = round(($weeklyPresent / $weeklyTotal) * 100, 1);

        // Monthly rate: last 30 days
        $monthStart = Carbon::today()->subDays(29);

        $monthlyPresent = Attendance::whereBetween('date', [$monthStart, $weekEnd])
            ->whereIn('status', ['present', 'late'])
            ->count();

        $monthlyTotal = $totalEmployees * 30;
        $monthlyRate = round(($monthlyPresent / $monthlyTotal) * 100, 1);

        return response()->json([
            'data' => [
                'weekly_rate' => $weeklyRate,
                'monthly_rate' => $monthlyRate,
            ]
        ]);
    }

    /**
     * GET /api/dashboard/department-breakdown
     * Number of employees per department
     */
    public function departmentBreakdown(): JsonResponse
    {
        $breakdown = Employee::selectRaw('department, count(*) as count')
            ->groupBy('department')
            ->orderBy('department')
            ->get()
            ->map(fn($item) => [
                'department' => $item->department,
                'count' => $item->count,
            ]);

        return response()->json([
            'data' => $breakdown,
        ]);
    }
}
