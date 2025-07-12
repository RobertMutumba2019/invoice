@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt me-2"></i>
        Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
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
});
</script>
@endpush 