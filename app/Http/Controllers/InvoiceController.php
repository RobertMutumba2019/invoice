<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\EfrisGood;
use App\Services\EfrisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    protected $efrisService;

    public function __construct(EfrisService $efrisService)
    {
        $this->efrisService = $efrisService;
    }

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['creator', 'items']);

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        $goods = EfrisGood::active()->get();

        return view('invoices.create', compact('goods'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $efrisConfig = app(\App\Services\EfrisService::class)->getEfrisConfig();
        $validator = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_tin' => [
                'nullable',
                function ($attribute, $value, $fail) use ($efrisConfig) {
                    if (
                        ($efrisConfig['buyer_type'] ?? '0') === '0' &&
                        ($efrisConfig['invoice_industry_code'] ?? '101') === '101' &&
                        ($efrisConfig['non_resident_flag'] ?? '0') === '0' &&
                        empty($value)
                    ) {
                        $fail('The buyer TIN is required for resident organizations in industry 101.');
                    }
                }
            ],
            'buyer_address' => 'nullable|string',
            'buyer_phone' => 'nullable|string|max:20',
            'buyer_email' => 'nullable|email',
            'currency' => 'required|string|max:3',
            'invoice_type' => 'required|in:LOCAL,EXPORT,CONTRACT,AUCTION',
            'invoice_date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.good_id' => 'required|exists:efris_goods,eg_id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $invoice = $this->efrisService->createInvoice($validator);

            DB::commit();

            return redirect()->route('invoices.show', $invoice->invoice_id)
                ->with('success', 'Invoice created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show($id)
    {
        $invoice = Invoice::with(['creator', 'items.good'])->findOrFail($id);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit($id)
    {
        $invoice = Invoice::with(['items'])->findOrFail($id);
        $goods = EfrisGood::active()->get();

        if ($invoice->status !== 'DRAFT') {
            return redirect()->route('invoices.show', $id)
                ->with('error', 'Only draft invoices can be edited');
        }

        return view('invoices.edit', compact('invoice', 'goods'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status !== 'DRAFT') {
            return redirect()->route('invoices.show', $id)
                ->with('error', 'Only draft invoices can be edited');
        }

        $efrisConfig = app(\App\Services\EfrisService::class)->getEfrisConfig();
        $validator = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_tin' => [
                'nullable',
                function ($attribute, $value, $fail) use ($efrisConfig) {
                    if (
                        ($efrisConfig['buyer_type'] ?? '0') === '0' &&
                        ($efrisConfig['invoice_industry_code'] ?? '101') === '101' &&
                        ($efrisConfig['non_resident_flag'] ?? '0') === '0' &&
                        empty($value)
                    ) {
                        $fail('The buyer TIN is required for resident organizations in industry 101.');
                    }
                }
            ],
            'buyer_address' => 'nullable|string',
            'buyer_phone' => 'nullable|string|max:20',
            'buyer_email' => 'nullable|email',
            'currency' => 'required|string|max:3',
            'invoice_type' => 'required|in:LOCAL,EXPORT,CONTRACT,AUCTION',
            'invoice_date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.good_id' => 'required|exists:efris_goods,eg_id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Update invoice details
            $invoice->update([
                'buyer_tin' => $validator['buyer_tin'],
                'buyer_name' => $validator['buyer_name'],
                'buyer_address' => $validator['buyer_address'],
                'buyer_phone' => $validator['buyer_phone'],
                'buyer_email' => $validator['buyer_email'],
                'currency' => $validator['currency'],
                'invoice_type' => $validator['invoice_type'],
                'invoice_date' => $validator['invoice_date'],
                'remarks' => $validator['remarks'],
            ]);

            // Remove existing items
            $invoice->items()->delete();

            // Add new items
            foreach ($validator['items'] as $itemData) {
                $this->efrisService->addItemToInvoice($invoice, $itemData);
            }

            // Recalculate totals
            $invoice->calculateTotals();

            DB::commit();

            return redirect()->route('invoices.show', $invoice->invoice_id)
                ->with('success', 'Invoice updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Submit invoice to EFRIS.
     */
    public function submitToEfris($id)
    {
        try {
            $invoice = Invoice::with('items')->findOrFail($id);

            if ($invoice->status !== 'DRAFT') {
                return back()->with('error', 'Only draft invoices can be submitted to EFRIS');
            }

            // Log the attempt
            \Log::info('Attempting to submit invoice to EFRIS', [
                'invoice_id' => $invoice->invoice_id,
                'invoice_no' => $invoice->invoice_no,
                'items_count' => $invoice->items->count(),
                'total_amount' => $invoice->total_amount
            ]);

            $result = $this->efrisService->submitToEfris($invoice);

            if ($result['success']) {
                return redirect()->route('invoices.show', $invoice->invoice_id)
                    ->with('success', $result['message']);
            } else {
                // Custom error handling for user-friendly messages
                $userMessage = $result['message'];
                if (str_contains($userMessage, 'buyerTin cannot be empty')) {
                    $userMessage = 'The Buyer TIN is required for this invoice. Please enter a valid TIN and try again.';
                }
                // Add more custom error parsing as needed

                \Log::error('Invoice submission failed', [
                    'invoice_id' => $invoice->invoice_id,
                    'result' => $result
                ]);
                return back()->with('error', $userMessage);
            }
        } catch (\Exception $e) {
            \Log::error('Exception during invoice submission', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified invoice.
     */
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status !== 'DRAFT') {
            return back()->with('error', 'Only draft invoices can be deleted');
        }

        try {
            DB::beginTransaction();

            $invoice->items()->delete();
            $invoice->delete();

            DB::commit();

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    /**
     * Get goods for AJAX request.
     */
    public function getGoods(Request $request)
    {
        $search = $request->get('search');

        $goods = EfrisGood::active()
            ->when($search, function ($query) use ($search) {
                $query->search($search);
            })
            ->limit(10)
            ->get(['eg_id', 'eg_name', 'eg_code', 'eg_price', 'eg_uom', 'eg_tax_category', 'eg_tax_rate']);

        return response()->json($goods);
    }

    /**
     * Print invoice.
     */
    public function print($id)
    {
        $invoice = Invoice::with(['creator', 'items.good'])->findOrFail($id);

        return view('invoices.print', compact('invoice'));
    }
}
