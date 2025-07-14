@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-shield me-2"></i>
        Edit Role: {{ $role->display_name }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('roles.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Roles
            </a>
            <a href="{{ route('roles.show', $role->id) }}" class="btn btn-sm btn-info">
                <i class="fas fa-eye me-1"></i>View Role
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
                    Role Information
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $role->name) }}" 
                                       placeholder="e.g., SALES_MANAGER" required>
                                <div class="form-text">Use uppercase letters and underscores (e.g., SALES_MANAGER)</div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                                       id="display_name" name="display_name" value="{{ old('display_name', $role->display_name) }}" 
                                       placeholder="e.g., Sales Manager" required>
                                <div class="form-text">Human-readable name for the role</div>
                                @error('display_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Describe the role's purpose and responsibilities">{{ old('description', $role->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Update Role
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
                    <i class="fas fa-key me-2"></i>
                    Permissions
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Select the permissions that will be assigned to this role.
                </p>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleAllPermissions()">
                        <label class="form-check-label" for="selectAll">
                            <strong>Select All Permissions</strong>
                        </label>
                    </div>
                </div>
                
                <div class="accordion" id="permissionsAccordion">
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $loop->index }}">
                                    <strong>{{ $module }}</strong>
                                    <span class="badge bg-primary ms-2">{{ count($modulePermissions) }}</span>
                                </button>
                            </h2>
                            <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse" 
                                 data-bs-parent="#permissionsAccordion">
                                <div class="accordion-body">
                                    @foreach($modulePermissions as $permission)
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" 
                                                   type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permission['id'] }}" 
                                                   id="permission_{{ $permission['id'] }}"
                                                   {{ in_array($permission['id'], $rolePermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="permission_{{ $permission['id'] }}">
                                                {{ $permission['display_name'] }}
                                                @if($permission['action'])
                                                    <small class="text-muted">({{ $permission['action'] }})</small>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Selected permissions: <span id="selectedCount">{{ count($rolePermissions) }}</span>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAllPermissions() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.permission-checkbox:checked');
    document.getElementById('selectedCount').textContent = checkboxes.length;
}

// Add event listeners to permission checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    updateSelectedCount();
    
    // Check if all permissions are selected
    const allCheckboxes = document.querySelectorAll('.permission-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.permission-checkbox:checked');
    const selectAll = document.getElementById('selectAll');
    
    if (allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length) {
        selectAll.checked = true;
    }
});

// Auto-format role name
document.getElementById('name').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '');
});
</script>
@endpush 