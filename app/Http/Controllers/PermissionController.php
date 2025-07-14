<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(Request $request)
    {
        $query = Permission::with(['roles']);

        // Filter by module
        if ($request->has('module') && $request->module) {
            $query->where('module', $request->module);
        }

        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $permissions = $query->orderBy('module')
            ->orderBy('display_name')
            ->paginate(20);

        $modules = Permission::getModules();
        $actions = Permission::getActions();

        return view('permissions.index', compact('permissions', 'modules', 'actions'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        $modules = Permission::getModules();
        $actions = Permission::getActions();
        
        return view('permissions.create', compact('modules', 'actions'));
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:permissions,name',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'module' => 'nullable|string|max:50',
            'action' => 'nullable|string|max:50',
        ]);

        try {
            $permission = Permission::create([
                'name' => strtoupper($request->name),
                'display_name' => $request->display_name,
                'description' => $request->description,
                'module' => $request->module,
                'action' => $request->action,
                'is_system' => false,
                'is_active' => true,
            ]);

            return redirect()->route('permissions.index')
                ->with('success', 'Permission created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create permission: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to create permission: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission)
    {
        $permission->load(['roles']);
        
        return view('permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        if ($permission->is_system) {
            return redirect()->route('permissions.index')
                ->with('error', 'System permissions cannot be edited');
        }

        $modules = Permission::getModules();
        $actions = Permission::getActions();
        
        return view('permissions.edit', compact('permission', 'modules', 'actions'));
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission)
    {
        if ($permission->is_system) {
            return redirect()->route('permissions.index')
                ->with('error', 'System permissions cannot be modified');
        }

        $request->validate([
            'name' => 'required|string|max:50|unique:permissions,name,' . $permission->id,
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'module' => 'nullable|string|max:50',
            'action' => 'nullable|string|max:50',
        ]);

        try {
            $permission->update([
                'name' => strtoupper($request->name),
                'display_name' => $request->display_name,
                'description' => $request->description,
                'module' => $request->module,
                'action' => $request->action,
            ]);

            return redirect()->route('permissions.index')
                ->with('success', 'Permission updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update permission: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update permission: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission)
    {
        if ($permission->is_system) {
            return redirect()->route('permissions.index')
                ->with('error', 'System permissions cannot be deleted');
        }

        if ($permission->roles()->count() > 0) {
            return redirect()->route('permissions.index')
                ->with('error', 'Cannot delete permission that is assigned to roles');
        }

        try {
            $permission->delete();
            
            return redirect()->route('permissions.index')
                ->with('success', 'Permission deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete permission: ' . $e->getMessage());
            
            return redirect()->route('permissions.index')
                ->with('error', 'Failed to delete permission: ' . $e->getMessage());
        }
    }

    /**
     * Toggle permission active status.
     */
    public function toggleStatus(Permission $permission)
    {
        if ($permission->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'System permissions cannot be deactivated'
            ], 400);
        }

        try {
            $permission->update(['is_active' => !$permission->is_active]);
            
            return response()->json([
                'success' => true,
                'message' => 'Permission status updated successfully',
                'is_active' => $permission->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle permission status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permission status'
            ], 500);
        }
    }

    /**
     * Get permissions grouped by module for API.
     */
    public function getGroupedPermissions()
    {
        $permissions = Permission::getGroupedByModule();
        
        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * Get roles that have this permission.
     */
    public function getRoles(Permission $permission)
    {
        $roles = $permission->roles()->select('roles.id', 'name', 'display_name')
            ->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * Bulk update permissions.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*.id' => 'required|exists:permissions,id',
            'permissions.*.is_active' => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->permissions as $permissionData) {
                $permission = Permission::find($permissionData['id']);
                if ($permission && !$permission->is_system) {
                    $permission->update(['is_active' => $permissionData['is_active']]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permissions updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk update permissions: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permissions'
            ], 500);
        }
    }
} 