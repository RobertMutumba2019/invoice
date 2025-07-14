@extends('layouts.app')

@section('title', 'Create Permission')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-key me-2"></i>
        Create New Permission
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Permissions
            </a>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Permission Information
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('permissions.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="e.g., USERS_CREATE" required>
                                <div class="form-text">Use uppercase letters and underscores (e.g., USERS_CREATE)</div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                                       id="display_name" name="display_name" value="{{ old('display_name') }}" 
                                       placeholder="e.g., Create Users" required>
                                <div class="form-text">Human-readable name for the permission</div>
                                @error('display_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="module" class="form-label">Module</label>
                                <select class="form-select @error('module') is-invalid @enderror" id="module" name="module">
                                    <option value="">Select Module</option>
                                    @foreach($modules as $module)
                                        <option value="{{ $module }}" {{ old('module') == $module ? 'selected' : '' }}>
                                            {{ $module }}
                                        </option>
                                    @endforeach
                                    <option value="custom" {{ old('module') == 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                                <div class="form-text">Group permissions by module</div>
                                @error('module')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="action" class="form-label">Action</label>
                                <select class="form-select @error('action') is-invalid @enderror" id="action" name="action">
                                    <option value="">Select Action</option>
                                    @foreach($actions as $action)
                                        <option value="{{ $action }}" {{ old('action') == $action ? 'selected' : '' }}>
                                            {{ $action }}
                                        </option>
                                    @endforeach
                                    <option value="custom" {{ old('action') == 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                                <div class="form-text">CRUD action or custom action</div>
                                @error('action')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Describe what this permission allows">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Create Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Help
                </h5>
            </div>
            <div class="card-body">
                <h6>Permission Naming Convention</h6>
                <p class="text-muted">Use the format: <code>MODULE_ACTION</code></p>
                <ul class="text-muted">
                    <li><strong>MODULE:</strong> The system module (e.g., USERS, INVOICES)</li>
                    <li><strong>ACTION:</strong> The action being performed (e.g., CREATE, READ, UPDATE, DELETE)</li>
                </ul>
                
                <h6>Examples</h6>
                <ul class="text-muted">
                    <li><code>USERS_CREATE</code> - Create new users</li>
                    <li><code>INVOICES_VIEW</code> - View invoices</li>
                    <li><code>REPORTS_EXPORT</code> - Export reports</li>
                    <li><code>SETTINGS_UPDATE</code> - Update system settings</li>
                </ul>
                
                <h6>Modules</h6>
                <p class="text-muted">Common modules in the system:</p>
                <ul class="text-muted">
                    <li>Dashboard</li>
                    <li>Users</li>
                    <li>Roles</li>
                    <li>Invoices</li>
                    <li>Credit Notes</li>
                    <li>Goods</li>
                    <li>Stocks</li>
                    <li>Customers</li>
                    <li>Departments</li>
                    <li>Designations</li>
                    <li>Reports</li>
                    <li>Settings</li>
                    <li>EFRIS</li>
                    <li>Audit</li>
                </ul>
                
                <h6>Actions</h6>
                <p class="text-muted">Common CRUD actions:</p>
                <ul class="text-muted">
                    <li>VIEW - Read/View data</li>
                    <li>CREATE - Create new records</li>
                    <li>UPDATE - Modify existing records</li>
                    <li>DELETE - Remove records</li>
                    <li>EXPORT - Export data</li>
                    <li>PRINT - Print documents</li>
                    <li>SUBMIT - Submit to external systems</li>
                    <li>TEST - Test connections</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-format permission name
document.getElementById('name').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '');
});

// Handle custom module input
document.getElementById('module').addEventListener('change', function(e) {
    if (this.value === 'custom') {
        const customModule = prompt('Enter custom module name:');
        if (customModule) {
            const option = new Option(customModule, customModule);
            this.add(option);
            this.value = customModule;
        } else {
            this.value = '';
        }
    }
});

// Handle custom action input
document.getElementById('action').addEventListener('change', function(e) {
    if (this.value === 'custom') {
        const customAction = prompt('Enter custom action name:');
        if (customAction) {
            const option = new Option(customAction, customAction);
            this.add(option);
            this.value = customAction;
        } else {
            this.value = '';
        }
    }
});
</script>
@endpush 