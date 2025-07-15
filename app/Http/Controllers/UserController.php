<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with(['department', 'designation', 'roles']);

        // Filter by department
        if ($request->has('department') && $request->department) {
            $query->where('user_department_id', $request->department);
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('user_active', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_surname', 'like', "%{$search}%")
                  ->orWhere('user_othername', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('user_surname')
            ->orderBy('user_othername')
            ->paginate(15);

        $departments = Department::orderBy('dept_name')->get();
        $roles = Role::active()->orderBy('display_name')->get();

        return view('users.index', compact('users', 'departments', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $departments = Department::orderBy('dept_name')->get();
        $designations = Designation::orderBy('designation_name')->get();
        $roles = Role::active()->orderBy('display_name')->get();
        
        return view('users.create', compact('departments', 'designations', 'roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:50|unique:users,user_name',
            'user_surname' => 'required|string|max:50',
            'user_othername' => 'nullable|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'user_phone' => 'nullable|string|max:20',
            'user_department_id' => 'nullable|exists:departments,dept_id',
            'user_designation' => 'nullable|exists:designations,designation_id',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Fix: Always set the 'name' field
        $user = User::create([
            'name' => $request->user_surname . ' ' . $request->user_othername,
            'user_name' => $request->user_name,
            'user_surname' => $request->user_surname,
            'user_othername' => $request->user_othername,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_phone' => $request->user_phone,
            'user_department_id' => $request->user_department_id,
            'user_designation' => $request->user_designation,
            'user_active' => true,
            'user_online' => false,
            'user_forgot_password' => false,
            'user_last_changed' => now(),
        ]);

        // Assign roles if provided
        if ($request->filled('roles')) {
            $user->roles()->sync($request->roles);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['department', 'designation', 'roles', 'accessRights']);
        
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $departments = Department::orderBy('dept_name')->get();
        $designations = Designation::orderBy('designation_name')->get();
        $roles = Role::active()->orderBy('display_name')->get();
        $userRoles = $user->roles()->pluck('roles.id')->toArray();
        $primaryRole = $user->primaryRole()->first();
        
        return view('users.edit', compact('user', 'departments', 'designations', 'roles', 'userRoles', 'primaryRole'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'user_name' => 'required|string|max:50|unique:users,user_name,' . $user->id,
            'user_surname' => 'required|string|max:50',
            'user_othername' => 'nullable|string|max:50',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'user_phone' => 'nullable|string|max:20',
            'user_department_id' => 'nullable|exists:departments,dept_id',
            'user_designation' => 'nullable|exists:designations,designation_id',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'primary_role' => 'nullable|exists:roles,id',
        ]);

        try {
            DB::beginTransaction();

            $userData = [
                'user_name' => $request->user_name,
                'user_surname' => $request->user_surname,
                'user_othername' => $request->user_othername,
                'email' => $request->email,
                'user_phone' => $request->user_phone,
                'user_department_id' => $request->user_department_id,
                'user_designation' => $request->user_designation,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Update roles
            if ($request->has('roles')) {
                $user->removeRoles($user->roles()->pluck('roles.id')->toArray());
                $user->assignRoles($request->roles);
                
                // Set primary role
                if ($request->primary_role) {
                    $user->setPrimaryRole($request->primary_role);
                }
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update user: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account');
        }

        try {
            $user->delete();
            
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete user: ' . $e->getMessage());
            
            return redirect()->route('users.index')
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account'
            ], 400);
        }

        try {
            $user->update(['user_active' => !$user->user_active]);
            
            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully',
                'user_active' => $user->user_active
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle user status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status'
            ], 500);
        }
    }

    /**
     * Get user roles for API.
     */
    public function getRoles(User $user)
    {
        $roles = $user->roles()->select('roles.id', 'name', 'display_name')
            ->withPivot('is_primary')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * Get user permissions for API.
     */
    public function getPermissions(User $user)
    {
        $permissions = $user->permissions()->select('id', 'name', 'display_name', 'module')
            ->get()
            ->groupBy('module');
        
        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * Bulk assign roles to users.
     */
    public function bulkAssignRoles(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->user_ids as $userId) {
                $user = User::find($userId);
                $user->assignRoles($request->role_ids);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Roles assigned successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk assign roles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign roles'
            ], 500);
        }
    }
} 