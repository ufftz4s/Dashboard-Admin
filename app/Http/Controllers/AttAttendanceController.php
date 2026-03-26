<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceCollection;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttAttendanceController extends Controller
{
    /**
     * GET /api/attendances
     * List attendance with filters and pagination
     */
    public function index(Request $request)
    {
        $query = Attendance::with('employee');

        if ($date = $request->get('date')) {
            $query->where('date', $date);
        }

        if ($employeeId = $request->get('employee_id')) {
            $query->where('employee_id', $employeeId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $perPage = $request->get('per_page', 15);
        $attendances = $query->orderByDesc('date')
            ->orderBy('employee_id')
            ->paginate($perPage);

        return new AttendanceCollection($attendances);
    }

    /**
     * POST /api/attendances
     * Record attendance
     */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $attendance = Attendance::create($request->validated());
        $attendance->load('employee');

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'data' => new AttendanceResource($attendance),
        ], 201);
    }

    /**
     * PUT /api/attendances/{id}
     * Update attendance
     */
    public function update(UpdateAttendanceRequest $request, $id): JsonResponse
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update($request->validated());
        $attendance->load('employee');

        return response()->json([
            'message' => 'Attendance updated successfully',
            'data' => new AttendanceResource($attendance),
        ]);
    }
}
