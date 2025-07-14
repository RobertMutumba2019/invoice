@extends('layouts.app')

@section('title', 'Edit Permission')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-key me-2"></i>
        Edit Permission: {{ $permission->display_name }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Permissions
            </a>
            <a href="{{ route('permissions.show', $permission->id) }}" class="btn btn-sm btn-info">
                <i class="fas fa-eye me-1"></i>View Permission
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
                <form action="{{ route('permissions.update', $permission->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $permission->name) }}" 
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
                                       id="display_name" name="display_name" value="{{ old('display_name', $permission->display_name) }}" 
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
                                        <option value="{{ $module }}" {{ old('module', $permission->module) == $module ? 'selected' : '' }}>
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
                                        <option value="{{ $action }}" {{ old('action', $permission->action) == $action ? 'selected' : '' }}>
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
                                  placeholder="Describe what this permission allows">{{ old('description', $permission->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Update Permission
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
                    Permission Details
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Type:</strong></td>
                        <td>
                            @if($permission->is_system)
                                <span class="badge bg-primary">System</span>
                            @else
                                <span class="badge bg-secondary">Custom</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            @if($permission->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $permission->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td>{{ $permission->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
                
                @if($permission->description)
                    <hr>
                    <h6>Current Description:</h6>
                    <p class="text-muted">{{ $permission->description }}</p>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Warning
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>System Permissions:</strong> System permissions cannot be modified as they are essential for the application to function properly.
                </div>
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