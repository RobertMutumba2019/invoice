<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class ActivateAllRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::query()->update(['is_active' => true]);
    }
} 