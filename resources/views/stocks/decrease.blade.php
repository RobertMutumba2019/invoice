@extends('layouts.app')

@section('title', 'Stock Decreases')

@section('content')
<div class="container-fluid">
    <div class="block-header">
        <div class="row">
            <div class="col">
                <h2>Stock Decreases</h2>
                <small class="text-muted">Manage stock decrease entries</small>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Stock Decrease Entries</h2>
                    <ul class="header-dropdown">
                        <li>
                            <a href="{{ route('stocks.decrease.create') }}" class="btn btn-primary">
                                <i class="zmdi zmdi-plus"></i> Add Stock Decrease
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
                            You have {{ $pendingCount }} pending stock decrease entries that need approval.
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
                                    <th>Decrease Reason</th>
                                    <th>Status</th>
                                    <th>Reference</th>
                                    <th>Created By</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockDecreases as $stockDecrease)
                                    <tr>
                                        <td>{{ $stockDecrease->id }}</td>
                                        <td>{{ $stockDecrease->item_code }}</td>
                                        <td>{{ $stockDecrease->good->eg_name ?? 'N/A' }}</td>
                                        <td>{{ number_format($stockDecrease->quantity, 2) }}</td>
                                        <td>{{ $stockDecrease->decrease_reason }}</td>
                                        <td>
                                            @if($stockDecrease->status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif($stockDecrease->status == 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @elseif($stockDecrease->status == 'rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($stockDecrease->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $stockDecrease->reference ?? 'N/A' }}</td>
                                        <td>{{ $stockDecrease->creator->name ?? 'N/A' }}</td>
                                        <td>{{ $stockDecrease->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('stocks.decrease.show', $stockDecrease->id) }}" 
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="zmdi zmdi-eye"></i>
                                                </a>
                                                @if($stockDecrease->status == 'pending')
                                                    <a href="{{ route('stocks.decrease.edit', $stockDecrease->id) }}" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="zmdi zmdi-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No stock decrease entries found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $stockDecreases->links() }}
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
        "order": [[ 8, "desc" ]]
    });
});
</script>
@endsection 