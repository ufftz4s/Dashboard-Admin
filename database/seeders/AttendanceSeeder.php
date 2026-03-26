<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            foreach ($employees as $employee) {
                $rand = rand(1, 100);

                if ($rand <= 70) {
                    // 70% present
                    $checkIn = sprintf('%02d:%02d', rand(7, 8), rand(0, 59));
                    $checkOut = sprintf('%02d:%02d', rand(16, 17), rand(0, 59));
                    $status = 'present';
                    $notes = null;
                } elseif ($rand <= 85) {
                    // 15% late
                    $checkIn = sprintf('%02d:%02d', rand(9, 10), rand(0, 59));
                    $checkOut = sprintf('%02d:%02d', rand(17, 18), rand(0, 59));
                    $status = 'late';
                    $notes = 'Arrived late - ' . collect(['traffic jam', 'overslept', 'family emergency', 'car trouble'])->random();
                } else {
                    // 15% absent
                    $checkIn = null;
                    $checkOut = null;
                    $status = 'absent';
                    $notes = collect(['Sick leave', 'Personal leave', 'Family matter', 'Medical appointment', 'No reason given'])->random();
                }

                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'status' => $status,
                    'notes' => $notes,
                ]);
            }
        }
    }
}
