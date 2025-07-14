@extends('layouts.app')

@section('title', 'Role Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-shield me-2"></i>
        Role Details: {{ $role->display_name }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('roles.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Roles
            </a>
            @if(!$role->is_system)
                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit me-1"></i>Edit Role
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Role Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>
                            <code>{{ $role->name }}</code>
                            @if($role->is_system)
                                <span class="badge bg-primary ms-1">System</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Display Name:</strong></td>
                        <td>{{ $role->display_name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Type:</strong></td>
                        <td>
                            @if($role->is_system)
                                <span class="badge bg-primary">System Role</span>
                            @else
                                <span class="badge bg-secondary">Custom Role</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            @if($role->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $role->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td>{{ $role->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
                
                @if($role->description)
                    <hr>
                    <h6>Description:</h6>
                    <p class="text-muted">{{ $role->description }}</p>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary">{{ $role->permissions->count() }}</h4>
                            <small class="text-muted">Permissions</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $role->users->count() }}</h4>
                        <small class="text-muted">Users</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="roleTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="permissions-tab" data-bs-toggle="tab" 
                                data-bs-target="#permissions" type="button" role="tab">
                            <i class="fas fa-key me-1"></i>Permissions ({{ $role->permissions->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="users-tab" data-bs-toggle="tab" 
                                data-bs-target="#users" type="button" role="tab">
                            <i class="fas fa-users me-1"></i>Users ({{ $role->users->count() }})
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="roleTabsContent">
                    <!-- Permissions Tab -->
                    <div class="tab-pane fade show active" id="permissions" role="tabpanel">
                        @if($role->permissions->count() > 0)
                            @php
                                $groupedPermissions = $role->permissions->groupBy('module');
                            @endphp
                            
                            @foreach($groupedPermissions as $module => $permissions)
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="fas fa-folder me-2"></i>{{ $module }}
                                        <span class="badge bg-primary ms-2">{{ $permissions->count() }}</span>
                                    </h6>
                                    <div class="row">
                                        @foreach($permissions as $permission)
                                            <div class="col-md-6 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <div>
                                                        <strong>{{ $permission->display_name }}</strong>
                                                        @if($permission->action)
                                                            <br><small class="text-muted">{{ $permission->action }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-key fa-2x mb-2 d-block"></i>
                                <p>No permissions assigned to this role</p>
                                @if(!$role->is_system)
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus me-1"></i>Assign Permissions
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                    
                    <!-- Users Tab -->
                    <div class="tab-pane fade" id="users" role="tabpanel">
                        @if($role->users->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                            <th>Primary Role</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($role->users as $user)
                                            <tr>
                                                <td>
                                                    <strong>{{ $user->user_surname }} {{ $user->user_othername }}</strong>
                                                    @if($user->pivot->is_primary)
                                                        <span class="badge bg-success ms-1">Primary</span>
                                                    @endif
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @if($user->department)
                                                        {{ $user->department->dept_name }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->pivot->is_primary)
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->user_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                <p>No users assigned to this role</p>
                                <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-user-plus me-1"></i>Manage Users
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$role->is_system)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Role Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Duplicate Role</h6>
                            <p class="text-muted">Create a copy of this role with all its permissions</p>
                            <button type="button" class="btn btn-outline-secondary" onclick="duplicateRole({{ $role->id }})">
                                <i class="fas fa-copy me-1"></i>Duplicate Role
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6>Delete Role</h6>
                            <p class="text-muted">Permanently delete this role (only if no users are assigned)</p>
                            <button type="button" class="btn btn-outline-danger" onclick="deleteRole({{ $role->id }})">
                                <i class="fas fa-trash me-1"></i>Delete Role
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

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
                <p>Are you sure you want to delete the role <strong>"{{ $role->display_name }}"</strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
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
                window.location.href = '/roles';
            } else {
                alert('Failed to duplicate role');
            }
        })
        .catch(error => {
            alert('Failed to duplicate role');
        });
    }
}

function deleteRole(roleId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    
    form.action = `/roles/${roleId}`;
    modal.show();
}
</script>
@endpush 