<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::with(['permissions', 'users'])
            ->orderBy('name')
            ->paginate(15);

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::active()->getGroupedByModule();
        
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => strtoupper($request->name),
                'display_name' => $request->display_name,
                'description' => $request->description,
                'is_system' => false,
                'is_active' => true,
            ]);

            if ($request->has('permissions')) {
                $role->assignPermissions($request->permissions);
            }

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create role: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to create role: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        
        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('roles.index')
                ->with('error', 'System roles cannot be edited');
        }

        $permissions = Permission::active()->getGroupedByModule();
        $rolePermissions = $role->permissions()->pluck('id')->toArray();
        
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('roles.index')
                ->with('error', 'System roles cannot be modified');
        }

        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            $role->update([
                'name' => strtoupper($request->name),
                'display_name' => $request->display_name,
                'description' => $request->description,
            ]);

            // Update permissions
            $permissions = $request->permissions ?? [];
            $role->assignPermissions($permissions);

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Role updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update role: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update role: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('roles.index')
                ->with('error', 'System roles cannot be deleted');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete role that has assigned users');
        }

        try {
            $role->delete();
            
            return redirect()->route('roles.index')
                ->with('success', 'Role deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete role: ' . $e->getMessage());
            
            return redirect()->route('roles.index')
                ->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }

    /**
     * Toggle role active status.
     */
    public function toggleStatus(Role $role)
    {
        if ($role->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'System roles cannot be deactivated'
            ], 400);
        }

        try {
            $role->update(['is_active' => !$role->is_active]);
            
            return response()->json([
                'success' => true,
                'message' => 'Role status updated successfully',
                'is_active' => $role->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle role status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role status'
            ], 500);
        }
    }

    /**
     * Get role permissions for API.
     */
    public function getPermissions(Role $role)
    {
        $permissions = $role->permissions()->select('id', 'name', 'display_name', 'module')->get();
        
        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * Get users with this role.
     */
    public function getUsers(Role $role)
    {
        $users = $role->users()->select('users.id', 'user_name', 'user_surname', 'user_othername', 'email')
            ->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Duplicate a role.
     */
    public function duplicate(Role $role)
    {
        try {
            DB::beginTransaction();

            $newRole = $role->replicate();
            $newRole->name = $role->name . '_COPY';
            $newRole->display_name = $role->display_name . ' (Copy)';
            $newRole->is_system = false;
            $newRole->save();

            // Copy permissions
            $permissionIds = $role->permissions()->pluck('permissions.id')->toArray();
            $newRole->assignPermissions($permissionIds);

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Role duplicated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to duplicate role: ' . $e->getMessage());
            
            return redirect()->route('roles.index')
                ->with('error', 'Failed to duplicate role: ' . $e->getMessage());
        }
    }
} 