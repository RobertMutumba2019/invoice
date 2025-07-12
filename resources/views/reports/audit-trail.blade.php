@extends('layouts.app')

@section('title', 'Audit Trail Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                        <li class="breadcrumb-item active">Audit Trail</li>
                    </ol>
                </div>
                <h4 class="page-title">Audit Trail Reports</h4>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.audit-trail') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="user_id" class="form-label">User</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">All Users</option>
                                @foreach(\App\Models\User::orderBy('user_surname')->get() as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="action" class="form-label">Action</label>
                            <select class="form-select" id="action" name="action">
                                <option value="">All Actions</option>
                                <option value="LOGIN" {{ request('action') == 'LOGIN' ? 'selected' : '' }}>Login</option>
                                <option value="LOGOUT" {{ request('action') == 'LOGOUT' ? 'selected' : '' }}>Logout</option>
                                <option value="CREATE" {{ request('action') == 'CREATE' ? 'selected' : '' }}>Create</option>
                                <option value="UPDATE" {{ request('action') == 'UPDATE' ? 'selected' : '' }}>Update</option>
                                <option value="DELETE" {{ request('action') == 'DELETE' ? 'selected' : '' }}>Delete</option>
                                <option value="EXPORT" {{ request('action') == 'EXPORT' ? 'selected' : '' }}>Export</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('reports.audit-trail') }}" class="btn btn-secondary">Clear</a>
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
                        <h5 class="card-title mb-0">Activity Log</h5>
                        <div>
                            <a href="{{ route('reports.export', ['type' => 'audit_trail', 'format' => 'csv'] + request()->query()) }}" 
                               class="btn btn-success">
                                <i class="mdi mdi-download"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Trail Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-striped dt-responsive nowrap w-100" id="audit-table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activities as $activity)
                                <tr>
                                    <td>
                                        <span class="text-muted">{{ $activity->created_at->format('M d, Y H:i:s') }}</span>
                                    </td>
                                    <td>
                                        @if($activity->user)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <span class="avatar-title bg-soft-primary rounded-circle">
                                                        {{ strtoupper(substr($activity->user->user_surname, 0, 1)) }}{{ strtoupper(substr($activity->user->user_firstname, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h5 class="font-14 my-1 fw-normal">{{ $activity->user->full_name }}</h5>
                                                    <span class="text-muted font-13">{{ $activity->user->user_name }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->action == 'LOGIN')
                                            <span class="badge bg-success">Login</span>
                                        @elseif($activity->action == 'LOGOUT')
                                            <span class="badge bg-secondary">Logout</span>
                                        @elseif($activity->action == 'CREATE')
                                            <span class="badge bg-primary">Create</span>
                                        @elseif($activity->action == 'UPDATE')
                                            <span class="badge bg-info">Update</span>
                                        @elseif($activity->action == 'DELETE')
                                            <span class="badge bg-danger">Delete</span>
                                        @elseif($activity->action == 'EXPORT')
                                            <span class="badge bg-warning">Export</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $activity->action }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-body">{{ $activity->description }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted font-13">{{ $activity->ip_address }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="row mt-3">
                        <div class="col-12">
                            {{ $activities->links() }}
                        </div>
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
    $('#audit-table').DataTable({
        "pageLength": 50,
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