<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            // Finance (3)
            ['name' => 'Sarah Johnson', 'department' => 'Finance', 'position' => 'Financial Analyst', 'email' => 'sarah.johnson@company.com', 'phone' => '081234567001'],
            ['name' => 'Michael Chen', 'department' => 'Finance', 'position' => 'Accountant', 'email' => 'michael.chen@company.com', 'phone' => '081234567002'],
            ['name' => 'Emily Davis', 'department' => 'Finance', 'position' => 'Finance Manager', 'email' => 'emily.davis@company.com', 'phone' => '081234567003'],

            // HR (3)
            ['name' => 'James Wilson', 'department' => 'HR', 'position' => 'HR Manager', 'email' => 'james.wilson@company.com', 'phone' => '081234567004'],
            ['name' => 'Lisa Anderson', 'department' => 'HR', 'position' => 'Recruiter', 'email' => 'lisa.anderson@company.com', 'phone' => '081234567005'],
            ['name' => 'Robert Martinez', 'department' => 'HR', 'position' => 'HR Coordinator', 'email' => 'robert.martinez@company.com', 'phone' => '081234567006'],

            // IT (4)
            ['name' => 'David Kim', 'department' => 'IT', 'position' => 'Software Engineer', 'email' => 'david.kim@company.com', 'phone' => '081234567007'],
            ['name' => 'Jennifer Lee', 'department' => 'IT', 'position' => 'DevOps Engineer', 'email' => 'jennifer.lee@company.com', 'phone' => '081234567008'],
            ['name' => 'William Brown', 'department' => 'IT', 'position' => 'IT Manager', 'email' => 'william.brown@company.com', 'phone' => '081234567009'],
            ['name' => 'Amanda Taylor', 'department' => 'IT', 'position' => 'QA Engineer', 'email' => 'amanda.taylor@company.com', 'phone' => '081234567010'],

            // Marketing (8)
            ['name' => 'Christopher Garcia', 'department' => 'Marketing', 'position' => 'Marketing Manager', 'email' => 'christopher.garcia@company.com', 'phone' => '081234567011'],
            ['name' => 'Jessica White', 'department' => 'Marketing', 'position' => 'Content Strategist', 'email' => 'jessica.white@company.com', 'phone' => '081234567012'],
            ['name' => 'Daniel Harris', 'department' => 'Marketing', 'position' => 'SEO Specialist', 'email' => 'daniel.harris@company.com', 'phone' => '081234567013'],
            ['name' => 'Sophia Clark', 'department' => 'Marketing', 'position' => 'Social Media Manager', 'email' => 'sophia.clark@company.com', 'phone' => '081234567014'],
            ['name' => 'Matthew Lewis', 'department' => 'Marketing', 'position' => 'Brand Designer', 'email' => 'matthew.lewis@company.com', 'phone' => '081234567015'],
            ['name' => 'Olivia Robinson', 'department' => 'Marketing', 'position' => 'Campaign Manager', 'email' => 'olivia.robinson@company.com', 'phone' => '081234567016'],
            ['name' => 'Andrew Walker', 'department' => 'Marketing', 'position' => 'Market Analyst', 'email' => 'andrew.walker@company.com', 'phone' => '081234567017'],
            ['name' => 'Emma Hall', 'department' => 'Marketing', 'position' => 'PR Coordinator', 'email' => 'emma.hall@company.com', 'phone' => '081234567018'],

            // Random (2)
            ['name' => 'Ryan Thompson', 'department' => 'IT', 'position' => 'System Administrator', 'email' => 'ryan.thompson@company.com', 'phone' => '081234567019'],
            ['name' => 'Megan Scott', 'department' => 'Finance', 'position' => 'Budget Analyst', 'email' => 'megan.scott@company.com', 'phone' => '081234567020'],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
