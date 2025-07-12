@extends('layouts.app')

@section('title', 'Invoice Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                        <li class="breadcrumb-item active">Invoice Reports</li>
                    </ol>
                </div>
                <h4 class="page-title">Invoice Reports</h4>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0" title="Total Invoices">Total Invoices</h5>
                            <h3 class="mt-3 mb-3">{{ $summary['total_invoices'] }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-primary rounded">
                                <i class="mdi mdi-file-document font-20 text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0" title="Total Amount">Total Amount</h5>
                            <h3 class="mt-3 mb-3">{{ number_format($summary['total_amount'], 2) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-success rounded">
                                <i class="mdi mdi-currency-usd font-20 text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0" title="Total Tax">Total Tax</h5>
                            <h3 class="mt-3 mb-3">{{ number_format($summary['total_tax'], 2) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-info rounded">
                                <i class="mdi mdi-calculator font-20 text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0" title="Status Breakdown">Status Breakdown</h5>
                            <div class="mt-3">
                                <span class="badge bg-warning me-1">{{ $summary['draft_count'] }} Draft</span>
                                <span class="badge bg-info me-1">{{ $summary['submitted_count'] }} Submitted</span>
                                <span class="badge bg-success">{{ $summary['approved_count'] }} Approved</span>
                            </div>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-warning rounded">
                                <i class="mdi mdi-chart-pie font-20 text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.invoices') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Submitted</option>
                                <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>Approved</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="STANDARD" {{ request('type') == 'STANDARD' ? 'selected' : '' }}>Standard</option>
                                <option value="SIMPLIFIED" {{ request('type') == 'SIMPLIFIED' ? 'selected' : '' }}>Simplified</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('reports.invoices') }}" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Button -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Invoice Data</h5>
                        <div>
                            <a href="{{ route('reports.export', ['type' => 'invoices', 'format' => 'csv'] + request()->query()) }}" 
                               class="btn btn-success">
                                <i class="mdi mdi-download"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-striped dt-responsive nowrap w-100" id="invoices-table">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Buyer Name</th>
                                    <th>Amount</th>
                                    <th>Tax</th>
                                    <th>Total</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice->invoice_id) }}" class="text-body fw-bold">
                                            {{ $invoice->invoice_no }}
                                        </a>
                                    </td>
                                    <td>{{ $invoice->buyer_name }}</td>
                                    <td>{{ number_format($invoice->invoice_amount, 2) }}</td>
                                    <td>{{ number_format($invoice->tax_amount, 2) }}</td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($invoice->total_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($invoice->invoice_type == 'STANDARD')
                                            <span class="badge bg-soft-primary text-primary">Standard</span>
                                        @else
                                            <span class="badge bg-soft-secondary text-secondary">Simplified</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->status == 'DRAFT')
                                            <span class="badge bg-warning">Draft</span>
                                        @elseif($invoice->status == 'SUBMITTED')
                                            <span class="badge bg-info">Submitted</span>
                                        @elseif($invoice->status == 'APPROVED')
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $invoice->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                    <td>{{ $invoice->creator->full_name ?? 'System' }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="{{ route('invoices.show', $invoice->invoice_id) }}" class="dropdown-item">
                                                    <i class="mdi mdi-eye me-1"></i>View
                                                </a>
                                                <a href="{{ route('invoices.print', $invoice->invoice_id) }}" class="dropdown-item">
                                                    <i class="mdi mdi-printer me-1"></i>Print
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#invoices-table').DataTable({
        "pageLength": 25,
        "order": [[7, "desc"]],
        "responsive": true,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        }
    });
});
</script>
@endpush 