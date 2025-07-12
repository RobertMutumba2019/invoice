@extends('layouts.app')

@section('title', 'Good/Service Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-box me-2"></i>
        Good/Service Details
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('goods.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Goods
        </a>
        @if(auth()->user()->hasAccess('GOODS', 'E'))
        <a href="{{ route('goods.edit', $good->eg_id) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Good Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Good/Service Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> {{ $good->eg_name }}</p>
                        <p><strong>Code:</strong> {{ $good->eg_code }}</p>
                        <p><strong>Price:</strong> {{ number_format($good->eg_price) }} UGX</p>
                        <p><strong>Unit of Measure:</strong> {{ $good->eg_uom }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Tax Category:</strong> 
                            <span class="badge bg-info">{{ $good->tax_category_name }}</span>
                        </p>
                        <p><strong>Tax Rate:</strong> {{ $good->eg_tax_rate }}%</p>
                        <p><strong>Status:</strong> 
                            @if($good->eg_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </p>
                        <p><strong>Added By:</strong> {{ $good->addedBy->full_name ?? 'System' }}</p>
                    </div>
                </div>
                
                @if($good->eg_description)
                <div class="mt-3">
                    <strong>Description:</strong>
                    <p class="mt-2">{{ $good->eg_description }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Usage Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Usage Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $good->invoiceItems->count() }}</h4>
                            <p class="text-muted">Times Used in Invoices</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-success">{{ number_format($good->invoiceItems->sum('quantity')) }}</h4>
                            <p class="text-muted">Total Quantity Sold</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-info">{{ number_format($good->invoiceItems->sum('total_amount')) }}</h4>
                            <p class="text-muted">Total Revenue (UGX)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($good->invoiceItems->count() > 0)
        <!-- Recent Usage -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Recent Usage in Invoices
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Date</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($good->invoiceItems->take(10) as $item)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $item->invoice_id) }}">
                                        {{ $item->invoice->invoice_no }}
                                    </a>
                                </td>
                                <td>{{ $item->invoice->invoice_date->format('M d, Y') }}</td>
                                <td>{{ number_format($item->quantity, 2) }} {{ $item->uom }}</td>
                                <td>{{ number_format($item->unit_price) }}</td>
                                <td>{{ number_format($item->total_amount) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Actions Sidebar -->
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(auth()->user()->hasAccess('GOODS', 'E'))
                    <a href="{{ route('goods.edit', $good->eg_id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Edit Good/Service
                    </a>
                    @endif
                    
                    <form method="POST" action="{{ route('goods.toggle-status', $good->eg_id) }}" class="d-grid">
                        @csrf
                        <button type="submit" class="btn btn-{{ $good->eg_active ? 'warning' : 'success' }}">
                            <i class="fas fa-{{ $good->eg_active ? 'pause' : 'play' }} me-1"></i>
                            {{ $good->eg_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    
                    @if(auth()->user()->hasAccess('GOODS', 'D') && $good->invoiceItems->count() == 0)
                    <form method="POST" action="{{ route('goods.destroy', $good->eg_id) }}" 
                          onsubmit="return confirm('Delete this good/service?')" class="d-grid">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 