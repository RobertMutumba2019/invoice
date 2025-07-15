@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@if (!isset($stats) || !isset($customerStats) || !isset($totalInvoices))
    <div class="alert alert-danger">
        <strong>Dashboard Error:</strong> One or more required variables are missing.<br>
        Please check that the DashboardController passes all required data to the view.<br>
        <ul>
            @if (!isset($stats))<li><code>$stats</code> is missing</li>@endif
            @if (!isset($customerStats))<li><code>$customerStats</code> is missing</li>@endif
            @if (!isset($totalInvoices))<li><code>$totalInvoices</code> is missing</li>@endif
        </ul>
    </div>
@else
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt me-2"></i>
        Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('customers.create') }}" class="btn btn-sm btn-success">
                <i class="fas fa-user-plus me-1"></i>Add Customer
            </a>
            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-users me-1"></i>View Customers
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($stats['total_invoices']) }}</div>
                        <div class="stats-label">Total Invoices</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($stats['total_amount_month']) }}</div>
                        <div class="stats-label">This Month (UGX)</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($stats['draft_invoices']) }}</div>
                        <div class="stats-label">Draft Invoices</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($stats['active_users']) }}</div>
                        <div class="stats-label">Active Users</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($stats['total_credit_notes'] ?? 0) }}</div>
                        <div class="stats-label">Credit Notes</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-undo"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($stats['total_stocks'] ?? 0) }}</div>
                        <div class="stats-label">Total Stock Records</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($stats['pending_stocks'] ?? 0) }}</div>
                        <div class="stats-label">Pending Stock</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($stats['total_stock_decreases'] ?? 0) }}</div>
                        <div class="stats-label">Stock Decreases</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-minus-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($totalCustomers) }}</div>
                        <div class="stats-label">Total Customers</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($customerStats['active']) }}</div>
                        <div class="stats-label">Active Customers</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($customerStats['over_limit']) }}</div>
                        <div class="stats-label">Over Credit Limit</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="stats-number">{{ number_format($customerStats['near_limit']) }}</div>
                        <div class="stats-label">Near Credit Limit</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon">
                            <i class="fas fa-exclamation-circle text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Monthly Invoice Trends
                </h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Invoice Status Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Customer Charts Row -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Customer Type Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="customerTypeChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-doughnut me-2"></i>
                    Customer Category Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="customerCategoryChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities and Invoices -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Recent Activities
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse($recentActivities as $activity)
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $activity->action }}</h6>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $activity->description }}</p>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                {{ $activity->user->full_name ?? 'System' }}
                            </small>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No recent activities</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice me-2"></i>
                    Recent Invoices
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse($recentInvoices as $invoice)
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $invoice->invoice_no }}</h6>
                                <span class="badge {{ $invoice->status_badge_class }}">
                                    {{ $invoice->status }}
                                </span>
                            </div>
                            <p class="mb-1">{{ $invoice->buyer_name }}</p>
                            <small class="text-muted">
                                <i class="fas fa-money-bill me-1"></i>
                                {{ number_format($invoice->total_amount) }} UGX
                                <span class="ms-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $invoice->created_at->format('M d, Y') }}
                                </span>
                            </small>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-file-invoice fa-2x mb-2"></i>
                            <p>No recent invoices</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Customers Section -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-tie me-2"></i>
                    Recent Customers
                </h5>
                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye me-1"></i>View All Customers
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Customer Code</th>
                                <th>Business Name</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Contact</th>
                                <th>Credit Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCustomers as $customer)
                                <tr>
                                    <td>{{ $customer->customer_code }}</td>
                                    <td>
                                        <a href="{{ route('customers.show', $customer->id) }}" class="text-decoration-none">
                                            {{ $customer->business_name }}
                                        </a>
                                    </td>
                                    <td>{{ $customer->customerTypeName }}</td>
                                    <td>{{ $customer->customerCategoryName }}</td>
                                    <td>
                                        @if($customer->contact_person)
                                            {{ $customer->contact_person }}<br>
                                        @endif
                                        @if($customer->email)
                                            <small class="text-muted">{{ $customer->email }}</small>
                                        @endif
                                    </td>
                                    <td>{!! $customer->credit_status_badge !!}</td>
                                    <td>{{ $customer->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('customers.show', $customer->id) }}" 
                                               class="btn btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('customers.edit', $customer->id) }}" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('customers.statement', $customer->id) }}" 
                                               class="btn btn-outline-success" title="Statement">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-user-tie fa-2x mb-2 d-block"></i>
                                        No customers found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Invoice Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: @json($monthlyInvoices['months']),
            datasets: [{
                label: 'Invoice Count',
                data: @json($monthlyInvoices['counts']),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Total Amount (UGX)',
                data: @json($monthlyInvoices['amounts']),
                borderColor: '#764ba2',
                backgroundColor: 'rgba(118, 75, 162, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Invoice Count'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Amount (UGX)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Draft', 'Submitted', 'Approved', 'Rejected', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $stats['draft_invoices'] }},
                    {{ $stats['submitted_invoices'] }},
                    {{ $stats['approved_invoices'] }},
                    0, // Rejected count
                    0  // Cancelled count
                ],
                backgroundColor: [
                    '#6c757d',
                    '#ffc107',
                    '#28a745',
                    '#dc3545',
                    '#343a40'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Customer Type Chart
    const customerTypeCtx = document.getElementById('customerTypeChart').getContext('2d');
    new Chart(customerTypeCtx, {
        type: 'pie',
        data: {
            labels: ['Individual', 'Company', 'Government', 'NGO'],
            datasets: [{
                data: [
                    {{ $customerStats['customers_by_type']['individual'] ?? 0 }},
                    {{ $customerStats['customers_by_type']['company'] ?? 0 }},
                    {{ $customerStats['customers_by_type']['government'] ?? 0 }},
                    {{ $customerStats['customers_by_type']['ngo'] ?? 0 }}
                ],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Customer Category Chart
    const customerCategoryCtx = document.getElementById('customerCategoryChart').getContext('2d');
    new Chart(customerCategoryCtx, {
        type: 'doughnut',
        data: {
            labels: ['Regular', 'Wholesale', 'Retail', 'Export', 'VIP'],
            datasets: [{
                data: [
                    {{ $customerStats['customers_by_category']['regular'] ?? 0 }},
                    {{ $customerStats['customers_by_category']['wholesale'] ?? 0 }},
                    {{ $customerStats['customers_by_category']['retail'] ?? 0 }},
                    {{ $customerStats['customers_by_category']['export'] ?? 0 }},
                    {{ $customerStats['customers_by_category']['vip'] ?? 0 }}
                ],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush 