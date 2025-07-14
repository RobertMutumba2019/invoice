@extends('layouts.app')

@section('title', 'Permissions Management')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-key me-2"></i>
        Permissions Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('permissions.create') }}" class="btn btn-sm btn-success">
                <i class="fas fa-plus me-1"></i>Create Permission
            </a>
            <a href="{{ route('roles.index') }}" class="btn btn-sm btn-info">
                <i class="fas fa-user-shield me-1"></i>Manage Roles
            </a>
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

<!-- Filters -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter me-2"></i>Filters
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('permissions.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="module" class="form-label">Module</label>
                <select class="form-select" id="module" name="module">
                    <option value="">All Modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                            {{ $module }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="action" class="form-label">Action</label>
                <select class="form-select" id="action" name="action">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ $action }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>
            All Permissions
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Permission Name</th>
                        <th>Display Name</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Type</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                        <tr>
                            <td>
                                <code>{{ $permission->name }}</code>
                                @if($permission->is_system)
                                    <span class="badge bg-primary ms-1">System</span>
                                @endif
                            </td>
                            <td>{{ $permission->display_name }}</td>
                            <td>
                                @if($permission->module)
                                    <span class="badge bg-info">{{ $permission->module }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($permission->action)
                                    <span class="badge bg-secondary">{{ $permission->action }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($permission->is_system)
                                    <span class="badge bg-primary">System</span>
                                @else
                                    <span class="badge bg-secondary">Custom</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-warning">{{ $permission->roles->count() }} roles</span>
                                @if($permission->roles->count() > 0)
                                    <button type="button" class="btn btn-sm btn-link" 
                                            onclick="showRoles({{ $permission->id }})">
                                        View
                                    </button>
                                @endif
                            </td>
                            <td>
                                @if($permission->is_system)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               {{ $permission->is_active ? 'checked' : '' }}
                                               onchange="toggleStatus({{ $permission->id }}, this.checked)"
                                               {{ $permission->is_system ? 'disabled' : '' }}>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('permissions.show', $permission->id) }}" 
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$permission->is_system)
                                        <a href="{{ route('permissions.edit', $permission->id) }}" 
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deletePermission({{ $permission->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-key fa-2x mb-2 d-block"></i>
                                No permissions found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($permissions->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $permissions->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Roles Modal -->
<div class="modal fade" id="rolesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-shield me-2"></i>Permission Roles
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="rolesContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading roles...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this permission? This action cannot be undone.</p>
                <p class="text-muted">If the permission is assigned to roles, it cannot be deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Permission</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(permissionId, isActive) {
    fetch(`/permissions/${permissionId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ is_active: isActive })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
        } else {
            // Revert the toggle
            event.target.checked = !isActive;
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        // Revert the toggle
        event.target.checked = !isActive;
        showAlert('error', 'Failed to update permission status');
    });
}

function showRoles(permissionId) {
    const modal = new bootstrap.Modal(document.getElementById('rolesModal'));
    const content = document.getElementById('rolesContent');
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading roles...</p>
        </div>
    `;
    
    modal.show();
    
    fetch(`/permissions/${permissionId}/roles`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Role Name</th><th>Display Name</th><th>Type</th></tr></thead><tbody>';
                
                if (data.data.data.length > 0) {
                    data.data.data.forEach(role => {
                        html += `
                            <tr>
                                <td><code>${role.name}</code></td>
                                <td>${role.display_name}</td>
                                <td>
                                    ${role.is_system ? 
                                        '<span class="badge bg-primary">System</span>' : 
                                        '<span class="badge bg-secondary">Custom</span>'
                                    }
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html += '<tr><td colspan="3" class="text-muted">No roles assigned</td></tr>';
                }
                
                html += '</tbody></table></div>';
                content.innerHTML = html;
            } else {
                content.innerHTML = '<p class="text-danger">Failed to load roles</p>';
            }
        })
        .catch(error => {
            content.innerHTML = '<p class="text-danger">Failed to load roles</p>';
        });
}

function deletePermission(permissionId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    
    form.action = `/permissions/${permissionId}`;
    modal.show();
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas fa-${icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.card'));
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endpush 