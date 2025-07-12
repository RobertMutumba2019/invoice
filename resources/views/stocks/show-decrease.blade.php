@extends('layouts.app')

@section('title', 'Stock Decrease Details')

@section('content')
<div class="container-fluid">
    <div class="block-header">
        <div class="row">
            <div class="col">
                <h2>Stock Decrease Details</h2>
                <small class="text-muted">View stock decrease entry information</small>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Stock Decrease Entry #{{ $stockDecrease->id }}</h2>
                    <ul class="header-dropdown">
                        @if($stockDecrease->status == 'pending')
                            <li>
                                <a href="{{ route('stocks.decrease.edit', $stockDecrease->id) }}" class="btn btn-warning">
                                    <i class="zmdi zmdi-edit"></i> Edit
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('stocks.decrease') }}" class="btn btn-secondary">
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
                                    <th width="150">Decrease ID:</th>
                                    <td>{{ $stockDecrease->id }}</td>
                                </tr>
                                <tr>
                                    <th>Item Code:</th>
                                    <td>{{ $stockDecrease->item_code }}</td>
                                </tr>
                                <tr>
                                    <th>Item Name:</th>
                                    <td>{{ $stockDecrease->good->eg_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Quantity:</th>
                                    <td>{{ number_format($stockDecrease->quantity, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Decrease Reason:</th>
                                    <td>{{ $stockDecrease->decrease_reason }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
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
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">SUN Reference:</th>
                                    <td>{{ $stockDecrease->sun_reference ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>EFRIS Reference:</th>
                                    <td>{{ $stockDecrease->reference ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>{{ $stockDecrease->creator->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created Date:</th>
                                    <td>{{ $stockDecrease->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated Date:</th>
                                    <td>{{ $stockDecrease->updated_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($stockDecrease->remarks)
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Remarks:</h5>
                                <p>{{ $stockDecrease->remarks }}</p>
                            </div>
                        </div>
                    @endif

                    @if($stockDecrease->status == 'pending')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="zmdi zmdi-info"></i>
                                    This stock decrease entry is pending approval. You can edit it or submit it to EFRIS for approval.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 