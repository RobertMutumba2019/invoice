@extends('layouts.app')

@section('title', 'Goods & Services')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-box me-2"></i>
        Goods & Services
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @if(auth()->user()->hasAccess('GOODS', 'A'))
        <a href="{{ route('goods.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Good/Service
        </a>
        @endif
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('goods.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Name, code...">
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">Tax Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <option value="V" {{ request('category') == 'V' ? 'selected' : '' }}>VAT</option>
                    <option value="Z" {{ request('category') == 'Z' ? 'selected' : '' }}>Zero Rated</option>
                    <option value="E" {{ request('category') == 'E' ? 'selected' : '' }}>Exempt</option>
                    <option value="D" {{ request('category') == 'D' ? 'selected' : '' }}>Deemed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="active" class="form-label">Status</label>
                <select class="form-select" id="active" name="active">
                    <option value="">All Status</option>
                    <option value="true" {{ request('active') === 'true' ? 'selected' : '' }}>Active</option>
                    <option value="false" {{ request('active') === 'false' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Goods Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>UOM</th>
                        <th>Tax Category</th>
                        <th>Tax Rate</th>
                        <th>Status</th>
                        <th>Added By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goods as $good)
                    <tr>
                        <td>
                            <strong>{{ $good->eg_code }}</strong>
                        </td>
                        <td>
                            <div>{{ $good->eg_name }}</div>
                            @if($good->eg_description)
                                <small class="text-muted">{{ Str::limit($good->eg_description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ number_format($good->eg_price) }}</strong> UGX
                        </td>
                        <td>{{ $good->eg_uom }}</td>
                        <td>
                            <span class="badge bg-info">{{ $good->tax_category_name }}</span>
                        </td>
                        <td>{{ $good->eg_tax_rate }}%</td>
                        <td>
                            @if($good->eg_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $good->addedBy->full_name ?? 'System' }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('goods.show', $good->eg_id) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('goods.qrcode', $good->eg_id) }}" class="btn btn-sm btn-outline-info" title="QR Code">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                                <a href="{{ route('goods.barcode', $good->eg_id) }}" class="btn btn-sm btn-outline-secondary" title="Barcode">
                                    <i class="fas fa-barcode"></i>
                                </a>
                                
                                @if(auth()->user()->hasAccess('GOODS', 'E'))
                                <a href="{{ route('goods.edit', $good->eg_id) }}" 
                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                
                                <form method="POST" action="{{ route('goods.toggle-status', $good->eg_id) }}" 
                                      class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $good->eg_active ? 'warning' : 'success' }}" 
                                            title="{{ $good->eg_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $good->eg_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                
                                @if(auth()->user()->hasAccess('GOODS', 'D'))
                                <form method="POST" action="{{ route('goods.destroy', $good->eg_id) }}" 
                                      class="d-inline" onsubmit="return confirm('Delete this good/service?')">
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
                        <td colspan="9" class="text-center py-4">
                            <i class="fas fa-box fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No goods/services found</p>
                            @if(auth()->user()->hasAccess('GOODS', 'A'))
                            <a href="{{ route('goods.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add First Good/Service
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($goods->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $goods->links() }}
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