<?php

namespace App\Http\Controllers;

use App\Models\EfrisGood;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoodsController extends Controller
{
    /**
     * Display a listing of goods.
     */
    public function index(Request $request)
    {
        $query = EfrisGood::with('addedBy');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->where('eg_tax_category', $request->category);
        }

        if ($request->filled('active')) {
            $query->where('eg_active', $request->active === 'true');
        }

        $goods = $query->orderBy('eg_name')->paginate(15);

        return view('goods.index', compact('goods'));
    }

    /**
     * Show the form for creating a new good.
     */
    public function create()
    {
        return view('goods.create');
    }

    /**
     * Store a newly created good.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'eg_name' => 'required|string|max:255',
            'eg_code' => 'required|string|max:50|unique:efris_goods,eg_code',
            'eg_description' => 'nullable|string',
            'eg_price' => 'required|numeric|min:0',
            'eg_uom' => 'required|string|max:20',
            'eg_tax_category' => 'required|in:V,Z,E,D',
            'eg_tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $good = EfrisGood::create(array_merge($request->all(), [
            'eg_added_by' => auth()->id(),
            'eg_date_added' => now(),
        ]));

        AuditTrail::register('GOOD_CREATED', "Good {$good->eg_name} created", 'efris_goods');

        return redirect()->route('goods.index')->with('success', 'Good created successfully');
    }

    /**
     * Display the specified good.
     */
    public function show($id)
    {
        $good = EfrisGood::with(['addedBy', 'invoiceItems'])->findOrFail($id);
        
        return view('goods.show', compact('good'));
    }

    /**
     * Show the form for editing the specified good.
     */
    public function edit($id)
    {
        $good = EfrisGood::findOrFail($id);
        
        return view('goods.edit', compact('good'));
    }

    /**
     * Update the specified good.
     */
    public function update(Request $request, $id)
    {
        $good = EfrisGood::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'eg_name' => 'required|string|max:255',
            'eg_code' => 'required|string|max:50|unique:efris_goods,eg_code,' . $id . ',eg_id',
            'eg_description' => 'nullable|string',
            'eg_price' => 'required|numeric|min:0',
            'eg_uom' => 'required|string|max:20',
            'eg_tax_category' => 'required|in:V,Z,E,D',
            'eg_tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $good->update($request->all());

        AuditTrail::register('GOOD_UPDATED', "Good {$good->eg_name} updated", 'efris_goods');

        return redirect()->route('goods.index')->with('success', 'Good updated successfully');
    }

    /**
     * Remove the specified good.
     */
    public function destroy($id)
    {
        $good = EfrisGood::findOrFail($id);
        $goodName = $good->eg_name;

        // Check if good is used in any invoices
        if ($good->invoiceItems()->count() > 0) {
            return back()->with('error', 'Cannot delete good that is used in invoices');
        }

        $good->delete();

        AuditTrail::register('GOOD_DELETED', "Good {$goodName} deleted", 'efris_goods');

        return redirect()->route('goods.index')->with('success', 'Good deleted successfully');
    }

    /**
     * Toggle good active status.
     */
    public function toggleStatus($id)
    {
        $good = EfrisGood::findOrFail($id);
        $good->update(['eg_active' => !$good->eg_active]);

        $status = $good->eg_active ? 'activated' : 'deactivated';
        AuditTrail::register('GOOD_STATUS_CHANGED', "Good {$good->eg_name} {$status}", 'efris_goods');

        return back()->with('success', "Good {$status} successfully");
    }
} 