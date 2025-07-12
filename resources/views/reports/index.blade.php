@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-bar me-2"></i>
        Reports
    </h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-file-invoice fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Invoice Reports</h5>
                <p class="card-text">Generate detailed reports on invoices, including summaries and analytics.</p>
                <a href="{{ route('reports.invoices') }}" class="btn btn-primary">
                    <i class="fas fa-chart-line me-1"></i>View Reports
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-success mb-3"></i>
                <h5 class="card-title">User Reports</h5>
                <p class="card-text">View user statistics, activity reports, and user management data.</p>
                <a href="{{ route('reports.users') }}" class="btn btn-success">
                    <i class="fas fa-user-chart me-1"></i>View Reports
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-history fa-3x text-info mb-3"></i>
                <h5 class="card-title">Audit Trail</h5>
                <p class="card-text">Track system activities, user actions, and security audit logs.</p>
                <a href="{{ route('reports.audit-trail') }}" class="btn btn-info">
                    <i class="fas fa-search me-1"></i>View Reports
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-download me-2"></i>
                    Export Reports
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('reports.export') }}" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label for="type" class="form-label">Report Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Select Report Type</option>
                            <option value="invoices">Invoice Reports</option>
                            <option value="users">User Reports</option>
                            <option value="audit_trail">Audit Trail</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="format" class="form-label">Export Format</label>
                        <select class="form-select" id="format" name="format">
                            <option value="csv">CSV</option>
                            <option value="xlsx">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-download me-1"></i>Export Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 