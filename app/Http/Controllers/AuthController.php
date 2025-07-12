<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\AccessRight;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = [
            'user_name' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            if (!$user->user_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'username' => ['Your account has been deactivated.'],
                ]);
            }

            $user->updateLastActivity();
            
            AuditTrail::register('LOGIN_SUCCESS', "User {$user->user_name} logged in successfully");

            return redirect()->intended('/dashboard');
        }

        throw ValidationException::withMessages([
            'username' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Handle logout request.
     */
    public function logout()
    {
        $user = Auth::user();
        if ($user) {
            $user->markOffline();
            AuditTrail::register('LOGOUT', "User {$user->user_name} logged out");
        }

        Auth::logout();
        return redirect('/login');
    }

    /**
     * Show user management page.
     */
    public function users()
    {
        $users = User::with(['department', 'designation'])->orderBy('user_surname')->get();
        $departments = Department::active()->get();
        $designations = Designation::active()->get();

        return view('auth.users', compact('users', 'departments', 'designations'));
    }

    /**
     * Show add user form.
     */
    public function showAddUser()
    {
        $departments = Department::active()->get();
        $designations = Designation::active()->get();

        return view('auth.add-user', compact('departments', 'designations'));
    }

    /**
     * Store new user.
     */
    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string|unique:users,user_name',
            'user_surname' => 'required|string',
            'user_othername' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'user_phone' => 'nullable|string',
            'user_department_id' => 'nullable|exists:departments,dept_id',
            'user_designation' => 'nullable|exists:designations,designation_id',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->user_surname . ' ' . $request->user_othername,
            'user_name' => $request->user_name,
            'user_surname' => $request->user_surname,
            'user_othername' => $request->user_othername,
            'email' => $request->email,
            'user_phone' => $request->user_phone,
            'user_department_id' => $request->user_department_id,
            'user_designation' => $request->user_designation,
            'password' => Hash::make($request->password),
            'user_last_changed' => now(),
        ]);

        AuditTrail::register('USER_CREATED', "User {$user->user_name} created");

        return redirect()->route('users')->with('success', 'User created successfully');
    }

    /**
     * Show edit user form.
     */
    public function showEditUser($id)
    {
        $user = User::findOrFail($id);
        $departments = Department::active()->get();
        $designations = Designation::active()->get();

        return view('auth.edit-user', compact('user', 'departments', 'designations'));
    }

    /**
     * Update user.
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string|unique:users,user_name,' . $id,
            'user_surname' => 'required|string',
            'user_othername' => 'nullable|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'user_phone' => 'nullable|string',
            'user_department_id' => 'nullable|exists:departments,dept_id',
            'user_designation' => 'nullable|exists:designations,designation_id',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = [
            'name' => $request->user_surname . ' ' . $request->user_othername,
            'user_name' => $request->user_name,
            'user_surname' => $request->user_surname,
            'user_othername' => $request->user_othername,
            'email' => $request->email,
            'user_phone' => $request->user_phone,
            'user_department_id' => $request->user_department_id,
            'user_designation' => $request->user_designation,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['user_last_changed'] = now();
        }

        $user->update($data);

        AuditTrail::register('USER_UPDATED', "User {$user->user_name} updated");

        return redirect()->route('users')->with('success', 'User updated successfully');
    }

    /**
     * Delete user.
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $userName = $user->user_name;
        
        $user->delete();

        AuditTrail::register('USER_DELETED', "User {$userName} deleted");

        return redirect()->route('users')->with('success', 'User deleted successfully');
    }

    /**
     * Show change password form.
     */
    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'user_last_changed' => now(),
            'user_forgot_password' => false,
        ]);

        AuditTrail::register('PASSWORD_CHANGED', "User {$user->user_name} changed password");

        return redirect()->route('dashboard')->with('success', 'Password changed successfully');
    }
} 