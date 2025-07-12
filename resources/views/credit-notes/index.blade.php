@extends('layouts.app')

@section('title', 'Credit Notes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Credit Notes</li>
                    </ol>
                </div>
                <h4 class="page-title">Credit Notes</h4>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('credit-notes.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Search by CN No, Invoice No, Buyer" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Submitted</option>
                                <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>Approved</option>
                                <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('credit-notes.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                        <div class="col-md-1 text-end">
                            <a href="{{ route('credit-notes.create') }}" class="btn btn-success"><i class="mdi mdi-plus"></i> New</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-striped dt-responsive nowrap w-100" id="credit-notes-table">
                            <thead>
                                <tr>
                                    <th>CN No</th>
                                    <th>Invoice No</th>
                                    <th>Buyer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($creditNotes as $cn)
                                <tr>
                                    <td><a href="{{ route('credit-notes.show', $cn->cn_id) }}" class="fw-bold">{{ $cn->cn_no }}</a></td>
                                    <td>{{ $cn->original_invoice_no }}</td>
                                    <td>{{ $cn->buyer_name }}</td>
                                    <td>{{ number_format($cn->total_amount, 2) }}</td>
                                    <td>
                                        @if($cn->status == 'DRAFT')
                                            <span class="badge bg-warning">Draft</span>
                                        @elseif($cn->status == 'SUBMITTED')
                                            <span class="badge bg-info">Submitted</span>
                                        @elseif($cn->status == 'APPROVED')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($cn->status == 'CANCELLED')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $cn->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $cn->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="{{ route('credit-notes.show', $cn->cn_id) }}" class="dropdown-item"><i class="mdi mdi-eye me-1"></i>View</a>
                                                @if($cn->status == 'DRAFT')
                                                    <a href="{{ route('credit-notes.edit', $cn->cn_id) }}" class="dropdown-item"><i class="mdi mdi-pencil me-1"></i>Edit</a>
                                                    <form method="POST" action="{{ route('credit-notes.destroy', $cn->cn_id) }}" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Delete this credit note?')"><i class="mdi mdi-delete me-1"></i>Delete</button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('credit-notes.print', $cn->cn_id) }}" class="dropdown-item"><i class="mdi mdi-printer me-1"></i>Print</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $creditNotes->links() }}
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
    $('#credit-notes-table').DataTable({
        "pageLength": 25,
        "order": [[0, "desc"]],
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