<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EfrisGood;
use App\Models\AccessRight;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create departments
        $departments = [
            ['dept_name' => 'Finance', 'dept_description' => 'Finance and Accounting Department'],
            ['dept_name' => 'IT', 'dept_description' => 'Information Technology Department'],
            ['dept_name' => 'Operations', 'dept_description' => 'Operations Department'],
            ['dept_name' => 'Human Resources', 'dept_description' => 'Human Resources Department'],
            ['dept_name' => 'Marketing', 'dept_description' => 'Marketing Department'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Create designations
        $designations = [
            ['designation_name' => 'Administrator', 'designation_description' => 'System Administrator'],
            ['designation_name' => 'Manager', 'designation_description' => 'Department Manager'],
            ['designation_name' => 'Supervisor', 'designation_description' => 'Team Supervisor'],
            ['designation_name' => 'Officer', 'designation_description' => 'Department Officer'],
            ['designation_name' => 'Assistant', 'designation_description' => 'Administrative Assistant'],
        ];

        foreach ($designations as $desig) {
            Designation::create($desig);
        }

        // Create admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'user_name' => 'admin',
            'user_surname' => 'System',
            'user_othername' => 'Administrator',
            'email' => 'admin@efris.com',
            'user_phone' => '2560778497936',
            'user_department_id' => Department::where('dept_name', 'IT')->first()->dept_id,
            'user_designation' => Designation::where('designation_name', 'Administrator')->first()->designation_id,
            'password' => Hash::make('Admin@2025'),
            'user_active' => true,
            'user_last_changed' => now(),
        ]);

        // Create access rights for admin
        $pages = ['USERS', 'INVOICES', 'GOODS', 'DEPARTMENTS', 'DESIGNATIONS', 'REPORTS', 'SETTINGS', 'STOCKS'];
        $rights = ['A', 'V', 'E', 'D']; // Add, View, Edit, Delete

        foreach ($pages as $page) {
            foreach ($rights as $right) {
                AccessRight::create([
                    'user_id' => $admin->id,
                    'page_name' => $page,
                    'right_type' => $right,
                    'active' => true,
                ]);
            }
        }

        // Create sample goods
        $goods = [
            [
                'eg_name' => 'Consultancy Services',
                'eg_code' => 'CS001',
                'eg_description' => 'Professional consultancy services',
                'eg_price' => 500000,
                'eg_uom' => 'BILLING',
                'eg_tax_category' => 'V',
                'eg_tax_rate' => 18.00,
            ],
            [
                'eg_name' => 'Training Services',
                'eg_code' => 'TS001',
                'eg_description' => 'Professional training services',
                'eg_price' => 300000,
                'eg_uom' => 'PER MONTH',
                'eg_tax_category' => 'V',
                'eg_tax_rate' => 18.00,
            ],
            [
                'eg_name' => 'Software License',
                'eg_code' => 'SL001',
                'eg_description' => 'Software licensing fees',
                'eg_price' => 1000000,
                'eg_uom' => 'UNIT',
                'eg_tax_category' => 'V',
                'eg_tax_rate' => 18.00,
            ],
            [
                'eg_name' => 'Exempt Service',
                'eg_code' => 'ES001',
                'eg_description' => 'Tax exempt service',
                'eg_price' => 200000,
                'eg_uom' => 'UNIT',
                'eg_tax_category' => 'E',
                'eg_tax_rate' => 0.00,
            ],
        ];

        foreach ($goods as $good) {
            EfrisGood::create(array_merge($good, [
                'eg_added_by' => $admin->id,
                'eg_date_added' => now(),
            ]));
        }

        // Create sample regular user
        $user = User::create([
            'name' => 'John Doe',
            'user_name' => 'johndoe',
            'user_surname' => 'Doe',
            'user_othername' => 'John',
            'email' => 'john.doe@efris.com',
            'user_phone' => '2560778497937',
            'user_department_id' => Department::where('dept_name', 'Finance')->first()->dept_id,
            'user_designation' => Designation::where('designation_name', 'Officer')->first()->designation_id,
            'password' => Hash::make('password'),
            'user_active' => true,
            'user_last_changed' => now(),
        ]);

        // Create limited access rights for regular user
        $userPages = ['INVOICES', 'GOODS'];
        foreach ($userPages as $page) {
            foreach (['V', 'E'] as $right) { // View and Edit only
                AccessRight::create([
                    'user_id' => $user->id,
                    'page_name' => $page,
                    'right_type' => $right,
                    'active' => true,
                ]);
            }
        }
    }
}
