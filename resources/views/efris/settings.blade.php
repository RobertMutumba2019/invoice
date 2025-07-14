@extends('layouts.app')

@section('title', 'EFRIS API Settings')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-cogs me-2"></i>
        EFRIS API Settings
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-success" onclick="testConnection()">
                <i class="fas fa-plug me-1"></i>Test Connection
            </button>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="validateConfig()">
                <i class="fas fa-check-circle me-1"></i>Validate Config
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning" onclick="getStatus()">
                <i class="fas fa-info-circle me-1"></i>Get Status
            </button>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cog me-2"></i>
                    EFRIS Configuration
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('efris.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="efris_api_url" class="form-label">
                                    <i class="fas fa-link me-1"></i>EFRIS API URL
                                </label>
                                <input type="url" class="form-control @error('efris_api_url') is-invalid @enderror" 
                                       id="efris_api_url" name="efris_api_url" 
                                       value="{{ old('efris_api_url', $settingsArray['efris_api_url'] ?? '') }}" 
                                       placeholder="https://efris.ura.go.ug/efrisapi/api/" required>
                                <div class="form-text">Base URL for EFRIS API endpoints</div>
                                @error('efris_api_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="efris_tin" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>EFRIS TIN
                                </label>
                                <input type="text" class="form-control @error('efris_tin') is-invalid @enderror" 
                                       id="efris_tin" name="efris_tin" 
                                       value="{{ old('efris_tin', $settingsArray['efris_tin'] ?? '') }}" 
                                       placeholder="1000023516" required>
                                <div class="form-text">Tax Identification Number for EFRIS</div>
                                @error('efris_tin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="efris_business_name" class="form-label">
                                    <i class="fas fa-building me-1"></i>Business Name
                                </label>
                                <input type="text" class="form-control @error('efris_business_name') is-invalid @enderror" 
                                       id="efris_business_name" name="efris_business_name" 
                                       value="{{ old('efris_business_name', $settingsArray['efris_business_name'] ?? '') }}" 
                                       placeholder="CIVIL AVIATION AUTHORITY" required>
                                <div class="form-text">Official business name for EFRIS</div>
                                @error('efris_business_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="efris_device_number" class="form-label">
                                    <i class="fas fa-desktop me-1"></i>Device Number
                                </label>
                                <input type="text" class="form-control @error('efris_device_number') is-invalid @enderror" 
                                       id="efris_device_number" name="efris_device_number" 
                                       value="{{ old('efris_device_number', $settingsArray['efris_device_number'] ?? '') }}" 
                                       placeholder="TCS5a2ce23154445074" required>
                                <div class="form-text">EFRIS device registration number</div>
                                @error('efris_device_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="efris_device_mac" class="form-label">
                                    <i class="fas fa-network-wired me-1"></i>Device MAC
                                </label>
                                <input type="text" class="form-control @error('efris_device_mac') is-invalid @enderror" 
                                       id="efris_device_mac" name="efris_device_mac" 
                                       value="{{ old('efris_device_mac', $settingsArray['efris_device_mac'] ?? '') }}" 
                                       placeholder="TCS2a80082879377106" required>
                                <div class="form-text">EFRIS device MAC address</div>
                                @error('efris_device_mac')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="efris_latitude" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Latitude
                                </label>
                                <input type="number" step="any" class="form-control @error('efris_latitude') is-invalid @enderror" 
                                       id="efris_latitude" name="efris_latitude" 
                                       value="{{ old('efris_latitude', $settingsArray['efris_latitude'] ?? '') }}" 
                                       placeholder="0.4061957" required>
                                <div class="form-text">Business location latitude</div>
                                @error('efris_latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="efris_longitude" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Longitude
                                </label>
                                <input type="number" step="any" class="form-control @error('efris_longitude') is-invalid @enderror" 
                                       id="efris_longitude" name="efris_longitude" 
                                       value="{{ old('efris_longitude', $settingsArray['efris_longitude'] ?? '') }}" 
                                       placeholder="32.643798" required>
                                <div class="form-text">Business location longitude</div>
                                @error('efris_longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="efris_default_currency" class="form-label">
                                    <i class="fas fa-money-bill-wave me-1"></i>Default Currency
                                </label>
                                <select class="form-select @error('efris_default_currency') is-invalid @enderror" 
                                        id="efris_default_currency" name="efris_default_currency" required>
                                    <option value="">Select Currency</option>
                                    <option value="UGX" {{ (old('efris_default_currency', $settingsArray['efris_default_currency'] ?? '') == 'UGX') ? 'selected' : '' }}>UGX - Uganda Shilling</option>
                                    <option value="USD" {{ (old('efris_default_currency', $settingsArray['efris_default_currency'] ?? '') == 'USD') ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ (old('efris_default_currency', $settingsArray['efris_default_currency'] ?? '') == 'EUR') ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ (old('efris_default_currency', $settingsArray['efris_default_currency'] ?? '') == 'GBP') ? 'selected' : '' }}>GBP - British Pound</option>
                                </select>
                                <div class="form-text">Default currency for invoices</div>
                                @error('efris_default_currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="efris_vat_rate" class="form-label">
                                    <i class="fas fa-percentage me-1"></i>VAT Rate (%)
                                </label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control @error('efris_vat_rate') is-invalid @enderror" 
                                       id="efris_vat_rate" name="efris_vat_rate" 
                                       value="{{ old('efris_vat_rate', $settingsArray['efris_vat_rate'] ?? '18') }}" 
                                       placeholder="18" required>
                                <div class="form-text">Default VAT rate percentage</div>
                                @error('efris_vat_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    API Status
                </h5>
            </div>
            <div class="card-body">
                <div id="apiStatus">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Checking API status...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    Recent Logs
                </h5>
            </div>
            <div class="card-body">
                <div id="recentLogs">
                    <div class="text-center">
                        <div class="spinner-border text-secondary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading logs...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Results Modal -->
<div class="modal fade" id="testResultsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plug me-2"></i>API Test Results
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="testResults"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load initial status
    getStatus();
    loadRecentLogs();
});

function testConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Testing...';
    button.disabled = true;
    
    fetch('{{ route("efris.test-connection") }}')
        .then(response => response.json())
        .then(data => {
            showTestResults('Connection Test', data);
        })
        .catch(error => {
            showTestResults('Connection Test', {
                success: false,
                message: 'Request failed: ' + error.message
            });
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

function validateConfig() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Validating...';
    button.disabled = true;
    
    fetch('{{ route("efris.validate-config") }}')
        .then(response => response.json())
        .then(data => {
            showTestResults('Configuration Validation', data);
        })
        .catch(error => {
            showTestResults('Configuration Validation', {
                success: false,
                message: 'Request failed: ' + error.message
            });
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

function getStatus() {
    console.log('getStatus function called');
    
    // Get the button that was clicked
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Update button state
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
    button.disabled = true;
    
    console.log('Fetching from:', '{{ route("efris.get-status") }}');
    
    // Make the API call
    fetch('{{ route("efris.get-status") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        updateApiStatus(data);
    })
    .catch(error => {
        console.error('Error fetching status:', error);
        updateApiStatus({
            success: false,
            message: 'Failed to get status: ' + error.message
        });
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function loadRecentLogs() {
    fetch('{{ route("efris.get-logs") }}')
        .then(response => response.json())
        .then(data => {
            updateRecentLogs(data);
        })
        .catch(error => {
            updateRecentLogs({
                success: false,
                message: 'Failed to load logs: ' + error.message
            });
        });
}

function showTestResults(title, data) {
    const modal = new bootstrap.Modal(document.getElementById('testResultsModal'));
    const resultsDiv = document.getElementById('testResults');
    
    let html = `<h6>${title}</h6>`;
    
    if (data.success) {
        html += `<div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>${data.message}
        </div>`;
    } else {
        html += `<div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>${data.message}
        </div>`;
    }
    
    if (data.errors) {
        html += `<div class="mt-3">
            <h6>Validation Errors:</h6>
            <ul class="list-unstyled">`;
        data.errors.forEach(error => {
            html += `<li><i class="fas fa-times text-danger me-2"></i>${error}</li>`;
        });
        html += `</ul></div>`;
    }
    
    if (data.data) {
        html += `<div class="mt-3">
            <h6>Response Data:</h6>
            <pre class="bg-light p-2 rounded"><code>${JSON.stringify(data.data, null, 2)}</code></pre>
        </div>`;
    }
    
    resultsDiv.innerHTML = html;
    modal.show();
}

function updateApiStatus(data) {
    const statusDiv = document.getElementById('apiStatus');
    
    if (data.success) {
        const config = data.data.config;
        const connection = data.data.connection;
        
        let html = `
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Connection Status:</span>
                    <span class="badge ${connection.success ? 'bg-success' : 'bg-danger'}">
                        ${connection.success ? 'Connected' : 'Failed'}
                    </span>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">API URL:</span>
                    <span class="text-muted">${config.api_url}</span>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">TIN:</span>
                    <span class="text-muted">${config.tin}</span>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Business:</span>
                    <span class="text-muted">${config.business_name}</span>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Device:</span>
                    <span class="text-muted">${config.device_number}</span>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Last Updated:</span>
                    <span class="text-muted">${data.data.last_updated}</span>
                </div>
            </div>
        `;
        
        statusDiv.innerHTML = html;
    } else {
        statusDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>${data.message}
            </div>
        `;
    }
}

function updateRecentLogs(data) {
    const logsDiv = document.getElementById('recentLogs');
    
    if (data.success && data.data.logs.length > 0) {
        let html = '<div class="list-group list-group-flush">';
        
        data.data.logs.slice(-10).reverse().forEach(log => {
            const isError = log.includes('ERROR') || log.includes('error');
            const isWarning = log.includes('WARNING') || log.includes('warning');
            
            let badgeClass = 'bg-secondary';
            if (isError) badgeClass = 'bg-danger';
            else if (isWarning) badgeClass = 'bg-warning';
            
            html += `
                <div class="list-group-item border-0 px-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <small class="text-muted">${log.substring(0, 19)}</small>
                        <span class="badge ${badgeClass}">${isError ? 'ERROR' : (isWarning ? 'WARN' : 'INFO')}</span>
                    </div>
                    <div class="mt-1">
                        <small class="text-muted">${log.substring(20)}</small>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        logsDiv.innerHTML = html;
    } else {
        logsDiv.innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>No recent EFRIS logs found</p>
            </div>
        `;
    }
}
</script>
@endpush 