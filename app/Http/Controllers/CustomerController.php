<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = Customer::with(['creator', 'updater']);

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            }
        }

        $customers = $query->orderBy('business_name')->paginate(15);
        
        $stats = [
            'total' => Customer::count(),
            'active' => Customer::active()->count(),
            'inactive' => Customer::inactive()->count(),
            'over_limit' => Customer::whereRaw('current_balance >= credit_limit')->count(),
        ];

        return view('customers.index', compact('customers', 'stats'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'tin_number' => 'nullable|string|max:50',
            'vrn_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_type' => 'required|in:INDIVIDUAL,COMPANY,GOVERNMENT,NGO',
            'customer_category' => 'required|in:REGULAR,WHOLESALE,RETAIL,EXPORT,VIP',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            
            $customer = Customer::create([
                'customer_code' => Customer::generateCustomerCode(),
                'business_name' => $request->business_name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'tin_number' => $request->tin_number,
                'vrn_number' => $request->vrn_number,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country ?? 'Uganda',
                'postal_code' => $request->postal_code,
                'customer_type' => $request->customer_type,
                'customer_category' => $request->customer_category,
                'credit_limit' => $request->credit_limit ?? 0,
                'payment_terms' => $request->payment_terms ?? 30,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'bank_branch' => $request->bank_branch,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            activity()->performedOn($customer)->log('Customer created');

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating customer: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified customer.
     */
    public function show($id)
    {
        $customer = Customer::with(['creator', 'updater', 'invoices', 'creditNotes'])->findOrFail($id);
        
        // Get customer statistics
        $stats = [
            'total_invoices' => $customer->invoices()->count(),
            'total_amount' => $customer->invoices()->sum('total_amount'),
            'paid_amount' => $customer->invoices()->where('status', 'PAID')->sum('total_amount'),
            'pending_amount' => $customer->invoices()->where('status', 'PENDING')->sum('total_amount'),
            'credit_notes' => $customer->creditNotes()->count(),
        ];

        return view('customers.show', compact('customer', 'stats'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'tin_number' => 'nullable|string|max:50',
            'vrn_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_type' => 'required|in:INDIVIDUAL,COMPANY,GOVERNMENT,NGO',
            'customer_category' => 'required|in:REGULAR,WHOLESALE,RETAIL,EXPORT,VIP',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            
            $customer->update([
                'business_name' => $request->business_name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'tin_number' => $request->tin_number,
                'vrn_number' => $request->vrn_number,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country ?? 'Uganda',
                'postal_code' => $request->postal_code,
                'customer_type' => $request->customer_type,
                'customer_category' => $request->customer_category,
                'credit_limit' => $request->credit_limit ?? 0,
                'payment_terms' => $request->payment_terms ?? 30,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'bank_branch' => $request->bank_branch,
                'notes' => $request->notes,
                'is_active' => $request->has('is_active'),
                'updated_by' => Auth::id(),
            ]);

            activity()->performedOn($customer)->log('Customer updated');

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating customer: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified customer.
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        // Check if customer has invoices
        if ($customer->invoices()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete customer with existing invoices.');
        }

        try {
            DB::beginTransaction();
            
            activity()->performedOn($customer)->log('Customer deleted');
            $customer->delete();

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }

    /**
     * Toggle customer active status.
     */
    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);
        
        try {
            $customer->update([
                'is_active' => !$customer->is_active,
                'updated_by' => Auth::id(),
            ]);

            $status = $customer->is_active ? 'activated' : 'deactivated';
            activity()->performedOn($customer)->log("Customer {$status}");

            return redirect()->back()->with('success', "Customer {$status} successfully.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating customer status: ' . $e->getMessage());
        }
    }

    /**
     * Search customers for AJAX requests.
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        $customers = Customer::active()
            ->search($search)
            ->select('id', 'customer_code', 'business_name', 'contact_person', 'tin_number')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    /**
     * Get customer statement.
     */
    public function statement($id)
    {
        $customer = Customer::findOrFail($id);
        
        $invoices = $customer->invoices()
            ->with(['items'])
            ->orderBy('created_at', 'desc')
            ->get();

        $creditNotes = $customer->creditNotes()
            ->with(['items'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customers.statement', compact('customer', 'invoices', 'creditNotes'));
    }
}
