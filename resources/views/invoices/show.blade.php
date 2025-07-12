@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-file-invoice me-2"></i>
        Invoice Details
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Invoices
        </a>
        @if($invoice->status === 'DRAFT' && auth()->user()->hasAccess('INVOICES', 'E'))
        <a href="{{ route('invoices.edit', $invoice->invoice_id) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        @endif
        <a href="{{ route('invoices.print', $invoice->invoice_id) }}" class="btn btn-info" target="_blank">
            <i class="fas fa-print me-1"></i>Print
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Invoice Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Invoice Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Invoice No:</strong> {{ $invoice->invoice_no }}</p>
                        @if($invoice->efris_invoice_no)
                        <p><strong>EFRIS No:</strong> {{ $invoice->efris_invoice_no }}</p>
                        @endif
                        <p><strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                        <p><strong>Type:</strong> 
                            <span class="badge bg-info">{{ $invoice->invoice_type }}</span>
                        </p>
                        <p><strong>Status:</strong> 
                            <span class="badge {{ $invoice->status_badge_class }}">{{ $invoice->status }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Currency:</strong> {{ $invoice->currency }}</p>
                        <p><strong>Created By:</strong> {{ $invoice->creator->full_name }}</p>
                        <p><strong>Created:</strong> {{ $invoice->created_at->format('M d, Y H:i') }}</p>
                        @if($invoice->updated_at != $invoice->created_at)
                        <p><strong>Updated:</strong> {{ $invoice->updated_at->format('M d, Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Buyer Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>
                    Buyer Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> {{ $invoice->buyer_name }}</p>
                        @if($invoice->buyer_tin)
                        <p><strong>TIN:</strong> {{ $invoice->buyer_tin }}</p>
                        @endif
                        @if($invoice->buyer_phone)
                        <p><strong>Phone:</strong> {{ $invoice->buyer_phone }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if($invoice->buyer_email)
                        <p><strong>Email:</strong> {{ $invoice->buyer_email }}</p>
                        @endif
                        @if($invoice->buyer_address)
                        <p><strong>Address:</strong> {{ $invoice->buyer_address }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>
                    Invoice Items
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Code</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Tax Rate</th>
                                <th>Tax Amount</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->item_code }}</td>
                                <td>{{ number_format($item->quantity, 2) }} {{ $item->uom }}</td>
                                <td>{{ number_format($item->unit_price) }}</td>
                                <td>{{ $item->tax_rate }}%</td>
                                <td>{{ number_format($item->tax_amount) }}</td>
                                <td><strong>{{ number_format($item->total_amount) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($invoice->remarks)
        <!-- Remarks -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-comment me-2"></i>
                    Remarks
                </h5>
            </div>
            <div class="card-body">
                <p>{{ $invoice->remarks }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Summary Sidebar -->
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    Invoice Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6">Subtotal:</div>
                    <div class="col-6 text-end">{{ number_format($invoice->invoice_amount) }} {{ $invoice->currency }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-6">Tax Amount:</div>
                    <div class="col-6 text-end">{{ number_format($invoice->tax_amount) }} {{ $invoice->currency }}</div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-6"><strong>Total:</strong></div>
                    <div class="col-6 text-end"><strong>{{ number_format($invoice->total_amount) }} {{ $invoice->currency }}</strong></div>
                </div>
                
                @if($invoice->status === 'DRAFT')
                <div class="d-grid gap-2">
                    <form method="POST" action="{{ route('invoices.submit-efris', $invoice->invoice_id) }}" 
                          onsubmit="return confirm('Submit this invoice to EFRIS?')">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane me-1"></i>Submit to EFRIS
                        </button>
                    </form>
                </div>
                @endif
                
                @if($invoice->status === 'APPROVED')
                <div class="d-grid gap-2">
                    <a href="{{ route('credit-notes.create', ['invoice_id' => $invoice->invoice_id]) }}" class="btn btn-warning">
                        <i class="fas fa-undo me-1"></i>Create Credit Note
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 