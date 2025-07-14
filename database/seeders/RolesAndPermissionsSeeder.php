<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $this->createPermissions();
        
        // Create roles
        $this->createRoles();
        
        // Assign permissions to roles
        $this->assignPermissionsToRoles();
        
        // Assign roles to existing users
        $this->assignRolesToUsers();
    }

    /**
     * Create system permissions.
     */
    private function createPermissions(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'DASHBOARD_VIEW', 'display_name' => 'View Dashboard', 'module' => 'Dashboard', 'action' => 'VIEW'],
            
            // User Management
            ['name' => 'USERS_VIEW', 'display_name' => 'View Users', 'module' => 'Users', 'action' => 'VIEW'],
            ['name' => 'USERS_CREATE', 'display_name' => 'Create Users', 'module' => 'Users', 'action' => 'CREATE'],
            ['name' => 'USERS_UPDATE', 'display_name' => 'Update Users', 'module' => 'Users', 'action' => 'UPDATE'],
            ['name' => 'USERS_DELETE', 'display_name' => 'Delete Users', 'module' => 'Users', 'action' => 'DELETE'],
            
            // Role Management
            ['name' => 'ROLES_VIEW', 'display_name' => 'View Roles', 'module' => 'Roles', 'action' => 'VIEW'],
            ['name' => 'ROLES_CREATE', 'display_name' => 'Create Roles', 'module' => 'Roles', 'action' => 'CREATE'],
            ['name' => 'ROLES_UPDATE', 'display_name' => 'Update Roles', 'module' => 'Roles', 'action' => 'UPDATE'],
            ['name' => 'ROLES_DELETE', 'display_name' => 'Delete Roles', 'module' => 'Roles', 'action' => 'DELETE'],
            
            // Invoice Management
            ['name' => 'INVOICES_VIEW', 'display_name' => 'View Invoices', 'module' => 'Invoices', 'action' => 'VIEW'],
            ['name' => 'INVOICES_CREATE', 'display_name' => 'Create Invoices', 'module' => 'Invoices', 'action' => 'CREATE'],
            ['name' => 'INVOICES_UPDATE', 'display_name' => 'Update Invoices', 'module' => 'Invoices', 'action' => 'UPDATE'],
            ['name' => 'INVOICES_DELETE', 'display_name' => 'Delete Invoices', 'module' => 'Invoices', 'action' => 'DELETE'],
            ['name' => 'INVOICES_SUBMIT', 'display_name' => 'Submit Invoices to EFRIS', 'module' => 'Invoices', 'action' => 'SUBMIT'],
            ['name' => 'INVOICES_PRINT', 'display_name' => 'Print Invoices', 'module' => 'Invoices', 'action' => 'PRINT'],
            
            // Credit Notes
            ['name' => 'CREDIT_NOTES_VIEW', 'display_name' => 'View Credit Notes', 'module' => 'Credit Notes', 'action' => 'VIEW'],
            ['name' => 'CREDIT_NOTES_CREATE', 'display_name' => 'Create Credit Notes', 'module' => 'Credit Notes', 'action' => 'CREATE'],
            ['name' => 'CREDIT_NOTES_UPDATE', 'display_name' => 'Update Credit Notes', 'module' => 'Credit Notes', 'action' => 'UPDATE'],
            ['name' => 'CREDIT_NOTES_DELETE', 'display_name' => 'Delete Credit Notes', 'module' => 'Credit Notes', 'action' => 'DELETE'],
            ['name' => 'CREDIT_NOTES_SUBMIT', 'display_name' => 'Submit Credit Notes to EFRIS', 'module' => 'Credit Notes', 'action' => 'SUBMIT'],
            
            // Goods Management
            ['name' => 'GOODS_VIEW', 'display_name' => 'View Goods', 'module' => 'Goods', 'action' => 'VIEW'],
            ['name' => 'GOODS_CREATE', 'display_name' => 'Create Goods', 'module' => 'Goods', 'action' => 'CREATE'],
            ['name' => 'GOODS_UPDATE', 'display_name' => 'Update Goods', 'module' => 'Goods', 'action' => 'UPDATE'],
            ['name' => 'GOODS_DELETE', 'display_name' => 'Delete Goods', 'module' => 'Goods', 'action' => 'DELETE'],
            
            // Stock Management
            ['name' => 'STOCKS_VIEW', 'display_name' => 'View Stock', 'module' => 'Stocks', 'action' => 'VIEW'],
            ['name' => 'STOCKS_CREATE', 'display_name' => 'Create Stock', 'module' => 'Stocks', 'action' => 'CREATE'],
            ['name' => 'STOCKS_UPDATE', 'display_name' => 'Update Stock', 'module' => 'Stocks', 'action' => 'UPDATE'],
            ['name' => 'STOCKS_DELETE', 'display_name' => 'Delete Stock', 'module' => 'Stocks', 'action' => 'DELETE'],
            
            // Customer Management
            ['name' => 'CUSTOMERS_VIEW', 'display_name' => 'View Customers', 'module' => 'Customers', 'action' => 'VIEW'],
            ['name' => 'CUSTOMERS_CREATE', 'display_name' => 'Create Customers', 'module' => 'Customers', 'action' => 'CREATE'],
            ['name' => 'CUSTOMERS_UPDATE', 'display_name' => 'Update Customers', 'module' => 'Customers', 'action' => 'UPDATE'],
            ['name' => 'CUSTOMERS_DELETE', 'display_name' => 'Delete Customers', 'module' => 'Customers', 'action' => 'DELETE'],
            
            // Department Management
            ['name' => 'DEPARTMENTS_VIEW', 'display_name' => 'View Departments', 'module' => 'Departments', 'action' => 'VIEW'],
            ['name' => 'DEPARTMENTS_CREATE', 'display_name' => 'Create Departments', 'module' => 'Departments', 'action' => 'CREATE'],
            ['name' => 'DEPARTMENTS_UPDATE', 'display_name' => 'Update Departments', 'module' => 'Departments', 'action' => 'UPDATE'],
            ['name' => 'DEPARTMENTS_DELETE', 'display_name' => 'Delete Departments', 'module' => 'Departments', 'action' => 'DELETE'],
            
            // Designation Management
            ['name' => 'DESIGNATIONS_VIEW', 'display_name' => 'View Designations', 'module' => 'Designations', 'action' => 'VIEW'],
            ['name' => 'DESIGNATIONS_CREATE', 'display_name' => 'Create Designations', 'module' => 'Designations', 'action' => 'CREATE'],
            ['name' => 'DESIGNATIONS_UPDATE', 'display_name' => 'Update Designations', 'module' => 'Designations', 'action' => 'UPDATE'],
            ['name' => 'DESIGNATIONS_DELETE', 'display_name' => 'Delete Designations', 'module' => 'Designations', 'action' => 'DELETE'],
            
            // Reports
            ['name' => 'REPORTS_VIEW', 'display_name' => 'View Reports', 'module' => 'Reports', 'action' => 'VIEW'],
            ['name' => 'REPORTS_EXPORT', 'display_name' => 'Export Reports', 'module' => 'Reports', 'action' => 'EXPORT'],
            
            // Settings
            ['name' => 'SETTINGS_VIEW', 'display_name' => 'View Settings', 'module' => 'Settings', 'action' => 'VIEW'],
            ['name' => 'SETTINGS_UPDATE', 'display_name' => 'Update Settings', 'module' => 'Settings', 'action' => 'UPDATE'],
            
            // EFRIS API
            ['name' => 'EFRIS_VIEW', 'display_name' => 'View EFRIS Settings', 'module' => 'EFRIS', 'action' => 'VIEW'],
            ['name' => 'EFRIS_UPDATE', 'display_name' => 'Update EFRIS Settings', 'module' => 'EFRIS', 'action' => 'UPDATE'],
            ['name' => 'EFRIS_TEST', 'display_name' => 'Test EFRIS Connection', 'module' => 'EFRIS', 'action' => 'TEST'],
            
            // Audit Trail
            ['name' => 'AUDIT_VIEW', 'display_name' => 'View Audit Trail', 'module' => 'Audit', 'action' => 'VIEW'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'display_name' => $permission['display_name'],
                    'module' => $permission['module'],
                    'action' => $permission['action'],
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Create system roles.
     */
    private function createRoles(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'is_system' => true,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'System administrator with most permissions',
                'is_system' => true,
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Department manager with limited administrative access',
                'is_system' => true,
            ],
            [
                'name' => 'accountant',
                'display_name' => 'Accountant',
                'description' => 'Financial operations and reporting',
                'is_system' => true,
            ],
            [
                'name' => 'operator',
                'display_name' => 'Operator',
                'description' => 'Basic operations and data entry',
                'is_system' => true,
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Read-only access to assigned modules',
                'is_system' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                [
                    'display_name' => $role['display_name'],
                    'description' => $role['description'],
                    'is_system' => $role['is_system'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Assign permissions to roles.
     */
    private function assignPermissionsToRoles(): void
    {
        // Super Admin - All permissions
        $superAdmin = Role::findByName('super_admin');
        $allPermissions = Permission::active()->pluck('id')->toArray();
        $superAdmin->assignPermissions($allPermissions);

        // Admin - Most permissions except role management
        $admin = Role::findByName('admin');
        $adminPermissions = Permission::active()
            ->whereNotIn('name', ['ROLES_CREATE', 'ROLES_UPDATE', 'ROLES_DELETE'])
            ->pluck('id')
            ->toArray();
        $admin->assignPermissions($adminPermissions);

        // Manager - Department and team management
        $manager = Role::findByName('manager');
        $managerPermissions = Permission::whereIn('name', [
            'DASHBOARD_VIEW',
            'INVOICES_VIEW', 'INVOICES_CREATE', 'INVOICES_UPDATE', 'INVOICES_PRINT',
            'CREDIT_NOTES_VIEW', 'CREDIT_NOTES_CREATE', 'CREDIT_NOTES_UPDATE',
            'GOODS_VIEW', 'GOODS_CREATE', 'GOODS_UPDATE',
            'STOCKS_VIEW', 'STOCKS_CREATE', 'STOCKS_UPDATE',
            'CUSTOMERS_VIEW', 'CUSTOMERS_CREATE', 'CUSTOMERS_UPDATE',
            'REPORTS_VIEW', 'REPORTS_EXPORT',
            'AUDIT_VIEW',
        ])->pluck('id')->toArray();
        $manager->assignPermissions($managerPermissions);

        // Accountant - Financial operations
        $accountant = Role::findByName('accountant');
        $accountantPermissions = Permission::whereIn('name', [
            'DASHBOARD_VIEW',
            'INVOICES_VIEW', 'INVOICES_CREATE', 'INVOICES_UPDATE', 'INVOICES_PRINT',
            'CREDIT_NOTES_VIEW', 'CREDIT_NOTES_CREATE', 'CREDIT_NOTES_UPDATE',
            'CUSTOMERS_VIEW', 'CUSTOMERS_CREATE', 'CUSTOMERS_UPDATE',
            'REPORTS_VIEW', 'REPORTS_EXPORT',
            'AUDIT_VIEW',
        ])->pluck('id')->toArray();
        $accountant->assignPermissions($accountantPermissions);

        // Operator - Basic operations
        $operator = Role::findByName('operator');
        $operatorPermissions = Permission::whereIn('name', [
            'DASHBOARD_VIEW',
            'INVOICES_VIEW', 'INVOICES_CREATE', 'INVOICES_PRINT',
            'GOODS_VIEW',
            'STOCKS_VIEW', 'STOCKS_CREATE',
            'CUSTOMERS_VIEW',
        ])->pluck('id')->toArray();
        $operator->assignPermissions($operatorPermissions);

        // Viewer - Read-only access
        $viewer = Role::findByName('viewer');
        $viewerPermissions = Permission::whereIn('name', [
            'DASHBOARD_VIEW',
            'INVOICES_VIEW',
            'CREDIT_NOTES_VIEW',
            'GOODS_VIEW',
            'STOCKS_VIEW',
            'CUSTOMERS_VIEW',
            'REPORTS_VIEW',
        ])->pluck('id')->toArray();
        $viewer->assignPermissions($viewerPermissions);
    }

    /**
     * Assign roles to existing users.
     */
    private function assignRolesToUsers(): void
    {
        // Get the admin role
        $adminRole = Role::findByName('admin');
        $superAdminRole = Role::findByName('super_admin');

        // Assign admin role to existing users (you can customize this logic)
        $users = User::all();
        
        foreach ($users as $user) {
            // Check if user already has roles
            if ($user->roles()->count() === 0) {
                // Assign admin role to users with admin-like names or emails
                if (stripos($user->email, 'admin') !== false || 
                    stripos($user->user_name, 'admin') !== false ||
                    $user->id === 1) { // First user is usually admin
                    $user->assignRoles([$superAdminRole->id], true);
                } else {
                    // Assign operator role to other users
                    $operatorRole = Role::findByName('operator');
                    $user->assignRoles([$operatorRole->id], true);
                }
            }
        }
    }
} 