<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DesignationController extends Controller
{
    /**
     * Display a listing of designations.
     */
    public function index()
    {
        $designations = Designation::with('addedBy')->orderBy('designation_name')->paginate(15);
        return view('designations.index', compact('designations'));
    }

    /**
     * Show the form for creating a new designation.
     */
    public function create()
    {
        return view('designations.create');
    }

    /**
     * Store a newly created designation.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'designation_name' => 'required|string|max:255|unique:designations,designation_name',
            'designation_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $designation = Designation::create(array_merge($request->all(), [
            'designation_added_by' => auth()->id(),
            'designation_date_added' => now(),
        ]));

        AuditTrail::register('DESIGNATION_CREATED', "Designation {$designation->designation_name} created", 'designations');

        return redirect()->route('designations.index')->with('success', 'Designation created successfully');
    }

    /**
     * Show the form for editing the specified designation.
     */
    public function edit($id)
    {
        $designation = Designation::findOrFail($id);
        return view('designations.edit', compact('designation'));
    }

    /**
     * Update the specified designation.
     */
    public function update(Request $request, $id)
    {
        $designation = Designation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'designation_name' => 'required|string|max:255|unique:designations,designation_name,' . $id . ',designation_id',
            'designation_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $designation->update($request->all());

        AuditTrail::register('DESIGNATION_UPDATED', "Designation {$designation->designation_name} updated", 'designations');

        return redirect()->route('designations.index')->with('success', 'Designation updated successfully');
    }

    /**
     * Remove the specified designation.
     */
    public function destroy($id)
    {
        $designation = Designation::findOrFail($id);
        $designationName = $designation->designation_name;

        // Check if designation has users
        if ($designation->users()->count() > 0) {
            return back()->with('error', 'Cannot delete designation that has users assigned');
        }

        $designation->delete();

        AuditTrail::register('DESIGNATION_DELETED', "Designation {$designationName} deleted", 'designations');

        return redirect()->route('designations.index')->with('success', 'Designation deleted successfully');
    }
} 