@extends('layouts.app')

@section('title', 'Stock Details')

@section('content')
<div class="container-fluid">
    <div class="block-header">
        <div class="row">
            <div class="col">
                <h2>Stock Details</h2>
                <small class="text-muted">View stock entry information</small>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Stock Entry #{{ $stock->id }}</h2>
                    <ul class="header-dropdown">
                        @if($stock->status == 'pending')
                            <li>
                                <a href="{{ route('stocks.increase', $stock->id) }}" class="btn btn-warning">
                                    <i class="zmdi zmdi-edit"></i> Edit
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('stocks.all') }}" class="btn btn-secondary">
                                <i class="zmdi zmdi-arrow-left"></i> Back to List
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

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Stock ID:</th>
                                    <td>{{ $stock->id }}</td>
                                </tr>
                                <tr>
                                    <th>Item Code:</th>
                                    <td>{{ $stock->item_code }}</td>
                                </tr>
                                <tr>
                                    <th>Item Name:</th>
                                    <td>{{ $stock->good->eg_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Quantity:</th>
                                    <td>{{ number_format($stock->quantity, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
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
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">SUN Reference:</th>
                                    <td>{{ $stock->sun_reference ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>EFRIS Reference:</th>
                                    <td>{{ $stock->reference ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>{{ $stock->creator->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created Date:</th>
                                    <td>{{ $stock->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated Date:</th>
                                    <td>{{ $stock->updated_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($stock->remarks)
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Remarks:</h5>
                                <p>{{ $stock->remarks }}</p>
                            </div>
                        </div>
                    @endif

                    @if($stock->status == 'pending')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="zmdi zmdi-info"></i>
                                    This stock entry is pending approval. You can edit it or submit it to EFRIS for approval.
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <a href="{{ route('stocks.qrcode', $stock->id) }}" class="btn btn-info me-2">
                                <i class="fas fa-qrcode me-1"></i> View QR Code
                            </a>
                            <a href="{{ route('stocks.barcode', $stock->id) }}" class="btn btn-secondary">
                                <i class="fas fa-barcode me-1"></i> View Barcode
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 