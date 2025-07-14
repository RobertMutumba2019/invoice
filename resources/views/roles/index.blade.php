@extends('layouts.app')

@section('title', 'Roles Management')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-shield me-2"></i>
        Roles Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('roles.create') }}" class="btn btn-sm btn-success">
                <i class="fas fa-plus me-1"></i>Create Role
            </a>
            <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-info">
                <i class="fas fa-key me-1"></i>Manage Permissions
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

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>
            All Roles
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Display Name</th>
                        <th>Type</th>
                        <th>Permissions</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>
                                <strong>{{ $role->name }}</strong>
                                @if($role->is_system)
                                    <span class="badge bg-primary ms-1">System</span>
                                @endif
                            </td>
                            <td>{{ $role->display_name }}</td>
                            <td>
                                @if($role->is_system)
                                    <span class="badge bg-primary">System</span>
                                @else
                                    <span class="badge bg-secondary">Custom</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $role->permissions->count() }} permissions</span>
                                @if($role->permissions->count() > 0)
                                    <button type="button" class="btn btn-sm btn-link" 
                                            onclick="showPermissions({{ $role->id }})">
                                        View
                                    </button>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-warning">{{ $role->users->count() }} users</span>
                                @if($role->users->count() > 0)
                                    <button type="button" class="btn btn-sm btn-link" 
                                            onclick="showUsers({{ $role->id }})">
                                        View
                                    </button>
                                @endif
                            </td>
                            <td>
                                @if($role->is_system)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               {{ $role->is_active ? 'checked' : '' }}
                                               onchange="toggleStatus({{ $role->id }}, this.checked)"
                                               {{ $role->is_system ? 'disabled' : '' }}>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $role->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('roles.show', $role->id) }}" 
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$role->is_system)
                                        <a href="{{ route('roles.edit', $role->id) }}" 
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary" 
                                                onclick="duplicateRole({{ $role->id }})" title="Duplicate">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deleteRole({{ $role->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-user-shield fa-2x mb-2 d-block"></i>
                                No roles found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($roles->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $roles->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key me-2"></i>Role Permissions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="permissionsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading permissions...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Modal -->
<div class="modal fade" id="usersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users me-2"></i>Role Users
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="usersContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading users...</p>
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
                <p>Are you sure you want to delete this role? This action cannot be undone.</p>
                <p class="text-muted">If the role has assigned users, it cannot be deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Role</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(roleId, isActive) {
    fetch(`/roles/${roleId}/toggle-status`, {
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
            // Show success message
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
        showAlert('error', 'Failed to update role status');
    });
}

function showPermissions(roleId) {
    const modal = new bootstrap.Modal(document.getElementById('permissionsModal'));
    const content = document.getElementById('permissionsContent');
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading permissions...</p>
        </div>
    `;
    
    modal.show();
    
    fetch(`/roles/${roleId}/permissions`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="row">';
                
                if (data.data.length > 0) {
                    const grouped = data.data.reduce((acc, permission) => {
                        if (!acc[permission.module]) {
                            acc[permission.module] = [];
                        }
                        acc[permission.module].push(permission);
                        return acc;
                    }, {});
                    
                    Object.keys(grouped).forEach(module => {
                        html += `
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary">${module}</h6>
                                <ul class="list-unstyled">`;
                        
                        grouped[module].forEach(permission => {
                            html += `<li><i class="fas fa-check text-success me-2"></i>${permission.display_name}</li>`;
                        });
                        
                        html += `</ul></div>`;
                    });
                } else {
                    html += '<div class="col-12"><p class="text-muted">No permissions assigned</p></div>';
                }
                
                html += '</div>';
                content.innerHTML = html;
            } else {
                content.innerHTML = '<p class="text-danger">Failed to load permissions</p>';
            }
        })
        .catch(error => {
            content.innerHTML = '<p class="text-danger">Failed to load permissions</p>';
        });
}

function showUsers(roleId) {
    const modal = new bootstrap.Modal(document.getElementById('usersModal'));
    const content = document.getElementById('usersContent');
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading users...</p>
        </div>
    `;
    
    modal.show();
    
    fetch(`/roles/${roleId}/users`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Name</th><th>Email</th></tr></thead><tbody>';
                
                if (data.data.data.length > 0) {
                    data.data.data.forEach(user => {
                        html += `
                            <tr>
                                <td>${user.user_surname} ${user.user_othername}</td>
                                <td>${user.email}</td>
                            </tr>
                        `;
                    });
                } else {
                    html += '<tr><td colspan="2" class="text-muted">No users assigned</td></tr>';
                }
                
                html += '</tbody></table></div>';
                content.innerHTML = html;
            } else {
                content.innerHTML = '<p class="text-danger">Failed to load users</p>';
            }
        })
        .catch(error => {
            content.innerHTML = '<p class="text-danger">Failed to load users</p>';
        });
}

function duplicateRole(roleId) {
    if (confirm('Are you sure you want to duplicate this role?')) {
        fetch(`/roles/${roleId}/duplicate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                showAlert('error', 'Failed to duplicate role');
            }
        })
        .catch(error => {
            showAlert('error', 'Failed to duplicate role');
        });
    }
}

function deleteRole(roleId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    
    form.action = `/roles/${roleId}`;
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