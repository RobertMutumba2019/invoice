@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="container-fluid">
    <div class="block-header">
        <div class="row">
            <div class="col">
                <h2>Customer Management</h2>
                <small class="text-muted">Manage all customers, credit limits, and statements</small>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-8">
            <form method="GET" action="{{ route('customers.index') }}" class="form-inline">
                <input type="text" name="search" class="form-control mr-2" placeholder="Search by name, code, TIN, email..." value="{{ request('search') }}">
                <select name="type" class="form-control mr-2">
                    <option value="">All Types</option>
                    <option value="INDIVIDUAL" {{ request('type') == 'INDIVIDUAL' ? 'selected' : '' }}>Individual</option>
                    <option value="COMPANY" {{ request('type') == 'COMPANY' ? 'selected' : '' }}>Company</option>
                    <option value="GOVERNMENT" {{ request('type') == 'GOVERNMENT' ? 'selected' : '' }}>Government</option>
                    <option value="NGO" {{ request('type') == 'NGO' ? 'selected' : '' }}>NGO</option>
                </select>
                <select name="category" class="form-control mr-2">
                    <option value="">All Categories</option>
                    <option value="REGULAR" {{ request('category') == 'REGULAR' ? 'selected' : '' }}>Regular</option>
                    <option value="WHOLESALE" {{ request('category') == 'WHOLESALE' ? 'selected' : '' }}>Wholesale</option>
                    <option value="RETAIL" {{ request('category') == 'RETAIL' ? 'selected' : '' }}>Retail</option>
                    <option value="EXPORT" {{ request('category') == 'EXPORT' ? 'selected' : '' }}>Export</option>
                    <option value="VIP" {{ request('category') == 'VIP' ? 'selected' : '' }}>VIP</option>
                </select>
                <select name="status" class="form-control mr-2">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn btn-primary mr-2"><i class="zmdi zmdi-search"></i> Search</button>
                <a href="{{ route('customers.create') }}" class="btn btn-success"><i class="zmdi zmdi-plus"></i> Add Customer</a>
            </form>
        </div>
        <div class="col-md-4 text-right">
            <div class="card">
                <div class="body p-2">
                    <strong>Total:</strong> {{ $stats['total'] }}<br>
                    <span class="text-success">Active:</span> {{ $stats['active'] }}<br>
                    <span class="text-danger">Inactive:</span> {{ $stats['inactive'] }}<br>
                    <span class="text-warning">Over Limit:</span> {{ $stats['over_limit'] }}
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Contact</th>
                                    <th>TIN</th>
                                    <th>Credit Limit</th>
                                    <th>Current Balance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                    <tr>
                                        <td>{{ $customer->customer_code }}</td>
                                        <td><a href="{{ route('customers.show', $customer->id) }}">{{ $customer->business_name }}</a></td>
                                        <td>{{ $customer->customerTypeName }}</td>
                                        <td>{{ $customer->customerCategoryName }}</td>
                                        <td>{{ $customer->contact_person }}<br>{{ $customer->email }}<br>{{ $customer->phone }}</td>
                                        <td>{{ $customer->tin_number }}</td>
                                        <td>{{ $customer->formatted_credit_limit }}</td>
                                        <td>{{ $customer->formatted_current_balance }}</td>
                                        <td>{!! $customer->status_badge !!}</td>
                                        <td>
                                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="zmdi zmdi-edit"></i></a>
                                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this customer?')" title="Delete"><i class="zmdi zmdi-delete"></i></button>
                                            </form>
                                            <form action="{{ route('customers.toggle-status', $customer->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary" title="Toggle Status">{{ $customer->is_active ? 'Deactivate' : 'Activate' }}</button>
                                            </form>
                                            <a href="{{ route('customers.statement', $customer->id) }}" class="btn btn-sm btn-info" title="Statement"><i class="zmdi zmdi-receipt"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No customers found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $customers->links() }}
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
        "order": [[ 1, "asc" ]]
    });
});
</script>
@endsection 