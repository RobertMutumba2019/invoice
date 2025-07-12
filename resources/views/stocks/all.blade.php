@extends('layouts.app')

@section('title', 'All Stock Management')

@section('content')
<div class="container-fluid">
    <div class="block-header">
        <div class="row">
            <div class="col">
                <h2>All Stock Management</h2>
                <small class="text-muted">Manage all stock entries and their status</small>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>All Stock Entries</h2>
                    <ul class="header-dropdown">
                        <li>
                            <a href="{{ route('stocks.create') }}" class="btn btn-primary">
                                <i class="zmdi zmdi-plus"></i> Add New Stock
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($pendingCount > 0)
                        <div class="alert alert-warning">
                            <i class="zmdi zmdi-alert-triangle"></i>
                            You have {{ $pendingCount }} pending stock entries that need approval.
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Reference</th>
                                    <th>Created By</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stocks as $stock)
                                    <tr>
                                        <td>{{ $stock->id }}</td>
                                        <td>{{ $stock->item_code }}</td>
                                        <td>{{ $stock->good->eg_name ?? 'N/A' }}</td>
                                        <td>{{ number_format($stock->quantity, 2) }}</td>
                                        <td>
                                            @if($stock->status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif($stock->status == 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @elseif($stock->status == 'rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($stock->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $stock->reference ?? 'N/A' }}</td>
                                        <td>{{ $stock->creator->name ?? 'N/A' }}</td>
                                        <td>{{ $stock->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('stocks.show', $stock->id) }}" 
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="zmdi zmdi-eye"></i>
                                                </a>
                                                @if($stock->status == 'pending')
                                                    <a href="{{ route('stocks.increase', $stock->id) }}" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="zmdi zmdi-edit"></i>
                                                    </a>
                                                @endif
                                                @if($stock->status == 'pending')
                                                    <form action="{{ route('stocks.destroy', $stock->id) }}" 
                                                          method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('Are you sure you want to delete this stock entry?')" 
                                                                title="Delete">
                                                            <i class="zmdi zmdi-delete"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No stock entries found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $stocks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.dataTable').DataTable({
        "pageLength": 25,
        "order": [[ 7, "desc" ]]
    });
});
</script>
@endsection 