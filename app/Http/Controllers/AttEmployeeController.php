<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeCollection;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttEmployeeController extends Controller
{
    /**
     * GET /api/employees
     * List all employees with pagination and search
     */
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('department', 'ilike', "%{$search}%")
                  ->orWhere('position', 'ilike', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $employees = $query->orderBy('name')->paginate($perPage);

        return new EmployeeCollection($employees);
    }

    /**
     * POST /api/employees
     * Create a new employee
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = Employee::create($request->validated());

        return response()->json([
            'message' => 'Employee created successfully',
            'data' => new EmployeeResource($employee),
        ], 201);
    }

    /**
     * PUT /api/employees/{id}
     * Update employee
     */
    public function update(UpdateEmployeeRequest $request, $id): JsonResponse
    {
        $employee = Employee::findOrFail($id);
        $employee->update($request->validated());

        return response()->json([
            'message' => 'Employee updated successfully',
            'data' => new EmployeeResource($employee),
        ]);
    }

    /**
     * DELETE /api/employees/{id}
     * Delete employee
     */
    public function destroy($id): JsonResponse
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json([
            'message' => 'Employee deleted successfully',
        ]);
    }
}
