@extends('layouts.app')

@section('title', 'Permission Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-key me-2"></i>
        Permission Details: {{ $permission->display_name }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Permissions
            </a>
            @if(!$permission->is_system)
                <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit me-1"></i>Edit Permission
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
                    Permission Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>
                            <code>{{ $permission->name }}</code>
                            @if($permission->is_system)
                                <span class="badge bg-primary ms-1">System</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Display Name:</strong></td>
                        <td>{{ $permission->display_name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Type:</strong></td>
                        <td>
                            @if($permission->is_system)
                                <span class="badge bg-primary">System Permission</span>
                            @else
                                <span class="badge bg-secondary">Custom Permission</span>
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
                        <td><strong>Module:</strong></td>
                        <td>
                            @if($permission->module)
                                <span class="badge bg-info">{{ $permission->module }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Action:</strong></td>
                        <td>
                            @if($permission->action)
                                <span class="badge bg-secondary">{{ $permission->action }}</span>
                            @else
                                <span class="text-muted">-</span>
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
                    <h6>Description:</h6>
                    <p class="text-muted">{{ $permission->description }}</p>
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
                    <div class="col-12">
                        <h4 class="text-primary">{{ $permission->roles->count() }}</h4>
                        <small class="text-muted">Roles with this permission</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-shield me-2"></i>
                    Roles with this Permission
                </h5>
            </div>
            <div class="card-body">
                @if($permission->roles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Role Name</th>
                                    <th>Display Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permission->roles as $role)
                                    <tr>
                                        <td>
                                            <code>{{ $role->name }}</code>
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
                                            @if($role->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ $role->users->count() }} users</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-user-shield fa-2x mb-2 d-block"></i>
                        <p>No roles have this permission</p>
                        <a href="{{ route('roles.index') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-user-shield me-1"></i>Manage Roles
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        @if(!$permission->is_system)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Permission Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Delete Permission</h6>
                            <p class="text-muted">Permanently delete this permission (only if no roles are assigned)</p>
                            <button type="button" class="btn btn-outline-danger" onclick="deletePermission({{ $permission->id }})">
                                <i class="fas fa-trash me-1"></i>Delete Permission
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
                <p>Are you sure you want to delete the permission <strong>"{{ $permission->display_name }}"</strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
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
function deletePermission(permissionId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    
    form.action = `/permissions/${permissionId}`;
    modal.show();
}
</script>
@endpush 