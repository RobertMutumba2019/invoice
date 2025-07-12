<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index()
    {
        $departments = Department::with('addedBy')->orderBy('dept_name')->paginate(15);
        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dept_name' => 'required|string|max:255|unique:departments,dept_name',
            'dept_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $department = Department::create(array_merge($request->all(), [
            'dept_added_by' => auth()->id(),
            'dept_date_added' => now(),
        ]));

        AuditTrail::register('DEPARTMENT_CREATED', "Department {$department->dept_name} created", 'departments');

        return redirect()->route('departments.index')->with('success', 'Department created successfully');
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit($id)
    {
        $department = Department::findOrFail($id);
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'dept_name' => 'required|string|max:255|unique:departments,dept_name,' . $id . ',dept_id',
            'dept_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $department->update($request->all());

        AuditTrail::register('DEPARTMENT_UPDATED', "Department {$department->dept_name} updated", 'departments');

        return redirect()->route('departments.index')->with('success', 'Department updated successfully');
    }

    /**
     * Remove the specified department.
     */
    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $deptName = $department->dept_name;

        // Check if department has users
        if ($department->users()->count() > 0) {
            return back()->with('error', 'Cannot delete department that has users assigned');
        }

        $department->delete();

        AuditTrail::register('DEPARTMENT_DELETED', "Department {$deptName} deleted", 'departments');

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully');
    }
} 