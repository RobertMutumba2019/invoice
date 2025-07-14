@extends('layouts.app')

@section('title', 'Available Stock')

@section('content')
<div class="container-fluid">
    <div class="block-header">
        <div class="row">
            <div class="col">
                <h2>Available Stock</h2>
                <small class="text-muted">View approved stock entries available for use</small>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Approved Stock Entries</h2>
                    <ul class="header-dropdown">
                        <li>
                            <a href="{{ route('stocks.all') }}" class="btn btn-info">
                                <i class="zmdi zmdi-list"></i> View All Stock
                            </a>
                        </li>
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

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
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
                                        <td>{{ $stock->reference ?? 'N/A' }}</td>
                                        <td>{{ $stock->creator->name ?? 'N/A' }}</td>
                                        <td>{{ $stock->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('stocks.show', $stock->id) }}" 
                                                   class="btn btn-sm btn-info" title="View Details">
                                                    <i class="zmdi zmdi-eye"></i>
                                                </a>
                                                <a href="{{ route('stocks.qrcode', $stock->id) }}" class="btn btn-sm btn-outline-info" title="QR Code">
                                                    <i class="fas fa-qrcode"></i>
                                                </a>
                                                <a href="{{ route('stocks.barcode', $stock->id) }}" class="btn btn-sm btn-outline-secondary" title="Barcode">
                                                    <i class="fas fa-barcode"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No approved stock entries found.</td>
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
        "order": [[ 6, "desc" ]]
    });
});
</script>
@endsection 