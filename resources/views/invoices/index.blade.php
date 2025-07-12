@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-file-invoice me-2"></i>
        Invoices
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @if(auth()->user()->hasAccess('INVOICES', 'A'))
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Create Invoice
        </a>
        @endif
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('invoices.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Invoice number, buyer name...">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                    <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Submitted</option>
                    <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>Approved</option>
                    <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                    <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label">Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="">All Types</option>
                    <option value="LOCAL" {{ request('type') == 'LOCAL' ? 'selected' : '' }}>Local</option>
                    <option value="EXPORT" {{ request('type') == 'EXPORT' ? 'selected' : '' }}>Export</option>
                    <option value="CONTRACT" {{ request('type') == 'CONTRACT' ? 'selected' : '' }}>Contract</option>
                    <option value="AUCTION" {{ request('type') == 'AUCTION' ? 'selected' : '' }}>Auction</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Invoice No.</th>
                        <th>Buyer Name</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <strong>{{ $invoice->invoice_no }}</strong>
                            @if($invoice->efris_invoice_no)
                                <br><small class="text-muted">EFRIS: {{ $invoice->efris_invoice_no }}</small>
                            @endif
                        </td>
                        <td>{{ $invoice->buyer_name }}</td>
                        <td>
                            <strong>{{ number_format($invoice->total_amount) }}</strong> {{ $invoice->currency }}
                            @if($invoice->tax_amount > 0)
                                <br><small class="text-muted">Tax: {{ number_format($invoice->tax_amount) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $invoice->invoice_type }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $invoice->status_badge_class }}">
                                {{ $invoice->status }}
                            </span>
                        </td>
                        <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                        <td>{{ $invoice->creator->full_name }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('invoices.show', $invoice->invoice_id) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($invoice->status === 'DRAFT' && auth()->user()->hasAccess('INVOICES', 'E'))
                                <a href="{{ route('invoices.edit', $invoice->invoice_id) }}" 
                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                
                                @if($invoice->status === 'DRAFT')
                                <form method="POST" action="{{ route('invoices.submit-efris', $invoice->invoice_id) }}" 
                                      class="d-inline" onsubmit="return confirm('Submit this invoice to EFRIS?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Submit to EFRIS">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </form>
                                @endif
                                
                                <a href="{{ route('invoices.print', $invoice->invoice_id) }}" 
                                   class="btn btn-sm btn-outline-secondary" title="Print" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                
                                @if($invoice->status === 'DRAFT' && auth()->user()->hasAccess('INVOICES', 'D'))
                                <form method="POST" action="{{ route('invoices.destroy', $invoice->invoice_id) }}" 
                                      class="d-inline" onsubmit="return confirm('Delete this invoice?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-file-invoice fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No invoices found</p>
                            @if(auth()->user()->hasAccess('INVOICES', 'A'))
                            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Create First Invoice
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($invoices->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .datatable {
        font-size: 0.875rem;
    }
    .btn-group .btn {
        margin-right: 0.25rem;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush 