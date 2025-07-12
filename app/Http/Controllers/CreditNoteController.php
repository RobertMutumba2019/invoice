<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\AuditTrail;
use App\Services\EfrisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditNoteController extends Controller
{
    protected $efrisService;

    public function __construct(EfrisService $efrisService)
    {
        $this->efrisService = $efrisService;
    }

    /**
     * Display a listing of credit notes.
     */
    public function index(Request $request)
    {
        $query = CreditNote::with(['creator', 'originalInvoice']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('cn_no', 'like', '%' . $request->search . '%')
                  ->orWhere('original_invoice_no', 'like', '%' . $request->search . '%')
                  ->orWhere('buyer_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $creditNotes = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('credit-notes.index', compact('creditNotes'));
    }

    /**
     * Show the form for creating a new credit note.
     */
    public function create(Request $request)
    {
        $invoiceId = $request->get('invoice_id');
        $invoice = null;

        if ($invoiceId) {
            $invoice = Invoice::with(['items.good'])->findOrFail($invoiceId);
            
            // Check if credit note already exists
            $existingCreditNote = CreditNote::where('original_invoice_id', $invoice->invoice_id)->first();
            if ($existingCreditNote) {
                return redirect()->route('credit-notes.show', $existingCreditNote->cn_id)
                    ->with('info', 'Credit note already exists for this invoice');
            }
        }

        return view('credit-notes.create', compact('invoice'));
    }

    /**
     * Store a newly created credit note.
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'original_invoice_id' => 'required|exists:invoices,invoice_id',
            'reason' => 'required|string|max:500',
            'reason_code' => 'required|string|max:10',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:invoice_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::with(['items'])->findOrFail($validator['original_invoice_id']);

            // Create credit note
            $creditNote = CreditNote::create([
                'cn_no' => CreditNote::generateCreditNoteNumber(),
                'original_invoice_id' => $invoice->invoice_id,
                'original_invoice_no' => $invoice->invoice_no,
                'buyer_name' => $invoice->buyer_name,
                'buyer_tin' => $invoice->buyer_tin,
                'buyer_address' => $invoice->buyer_address,
                'buyer_phone' => $invoice->buyer_phone,
                'buyer_email' => $invoice->buyer_email,
                'currency' => $invoice->currency,
                'reason' => $validator['reason'],
                'reason_code' => $validator['reason_code'],
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
            ]);

            // Add items to credit note
            foreach ($validator['items'] as $itemData) {
                $invoiceItem = InvoiceItem::findOrFail($itemData['item_id']);
                
                CreditNoteItem::createFromInvoiceItem(
                    $creditNote, 
                    $invoiceItem, 
                    $itemData['quantity']
                );
            }

            // Calculate totals
            $creditNote->calculateTotals();

            AuditTrail::register('CREDIT_NOTE_CREATED', "Credit note {$creditNote->cn_no} created for invoice {$invoice->invoice_no}", 'credit_notes');

            DB::commit();

            return redirect()->route('credit-notes.show', $creditNote->cn_id)
                ->with('success', 'Credit note created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create credit note: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified credit note.
     */
    public function show($id)
    {
        $creditNote = CreditNote::with(['creator', 'originalInvoice', 'items.originalItem'])->findOrFail($id);
        
        return view('credit-notes.show', compact('creditNote'));
    }

    /**
     * Show the form for editing the specified credit note.
     */
    public function edit($id)
    {
        $creditNote = CreditNote::with(['items.originalItem'])->findOrFail($id);

        if ($creditNote->status !== 'DRAFT') {
            return redirect()->route('credit-notes.show', $id)
                ->with('error', 'Only draft credit notes can be edited');
        }

        return view('credit-notes.edit', compact('creditNote'));
    }

    /**
     * Update the specified credit note.
     */
    public function update(Request $request, $id)
    {
        $creditNote = CreditNote::findOrFail($id);

        if ($creditNote->status !== 'DRAFT') {
            return redirect()->route('credit-notes.show', $id)
                ->with('error', 'Only draft credit notes can be edited');
        }

        $validator = $request->validate([
            'reason' => 'required|string|max:500',
            'reason_code' => 'required|string|max:10',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:credit_note_items,cni_id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            // Update credit note details
            $creditNote->update([
                'reason' => $validator['reason'],
                'reason_code' => $validator['reason_code'],
                'updated_by' => auth()->id(),
            ]);

            // Update items
            foreach ($validator['items'] as $itemData) {
                $creditNoteItem = CreditNoteItem::findOrFail($itemData['item_id']);
                $creditNoteItem->update([
                    'quantity' => $itemData['quantity'],
                ]);
                $creditNoteItem->calculateTotals();
            }

            // Recalculate totals
            $creditNote->calculateTotals();

            AuditTrail::register('CREDIT_NOTE_UPDATED', "Credit note {$creditNote->cn_no} updated", 'credit_notes');

            DB::commit();

            return redirect()->route('credit-notes.show', $creditNote->cn_id)
                ->with('success', 'Credit note updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update credit note: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Submit credit note to EFRIS.
     */
    public function submitToEfris($id)
    {
        $creditNote = CreditNote::with(['items', 'originalInvoice'])->findOrFail($id);

        if (!$creditNote->canBeSubmitted()) {
            return back()->with('error', 'Credit note cannot be submitted to EFRIS');
        }

        $result = $this->efrisService->submitCreditNote($creditNote);

        if ($result['success']) {
            return redirect()->route('credit-notes.show', $creditNote->cn_id)
                ->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }

    /**
     * Cancel credit note.
     */
    public function cancel($id)
    {
        $creditNote = CreditNote::findOrFail($id);

        if (!$creditNote->canBeCancelled()) {
            return back()->with('error', 'Credit note cannot be cancelled');
        }

        try {
            $creditNote->update([
                'status' => 'CANCELLED',
                'updated_by' => auth()->id(),
            ]);

            AuditTrail::register('CREDIT_NOTE_CANCELLED', "Credit note {$creditNote->cn_no} cancelled", 'credit_notes');

            return redirect()->route('credit-notes.show', $creditNote->cn_id)
                ->with('success', 'Credit note cancelled successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel credit note: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified credit note.
     */
    public function destroy($id)
    {
        $creditNote = CreditNote::findOrFail($id);

        if ($creditNote->status !== 'DRAFT') {
            return back()->with('error', 'Only draft credit notes can be deleted');
        }

        try {
            DB::beginTransaction();

            $creditNote->items()->delete();
            $creditNote->delete();

            AuditTrail::register('CREDIT_NOTE_DELETED', "Credit note {$creditNote->cn_no} deleted", 'credit_notes');

            DB::commit();

            return redirect()->route('credit-notes.index')
                ->with('success', 'Credit note deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete credit note: ' . $e->getMessage());
        }
    }

    /**
     * Print credit note.
     */
    public function print($id)
    {
        $creditNote = CreditNote::with(['creator', 'originalInvoice', 'items.originalItem'])->findOrFail($id);
        
        return view('credit-notes.print', compact('creditNote'));
    }

    /**
     * Get invoice items for AJAX request.
     */
    public function getInvoiceItems($invoiceId)
    {
        $invoice = Invoice::with(['items.good'])->findOrFail($invoiceId);
        
        return response()->json($invoice->items);
    }
} 