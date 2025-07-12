@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users me-2"></i>
        User Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @if(auth()->user()->hasAccess('USERS', 'A'))
        <a href="{{ route('users.add') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add User
        </a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div>{{ $user->full_name }}</div>
                            @if($user->user_phone)
                                <small class="text-muted">{{ $user->user_phone }}</small>
                            @endif
                        </td>
                        <td>{{ $user->user_name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->department->dept_name ?? 'Not Assigned' }}</td>
                        <td>{{ $user->designation->designation_name ?? 'Not Assigned' }}</td>
                        <td>
                            @if($user->user_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                            @if($user->user_online)
                                <span class="badge bg-info">Online</span>
                            @endif
                        </td>
                        <td>
                            @if($user->user_last_logged_in)
                                {{ $user->user_last_logged_in->format('M d, Y H:i') }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @if(auth()->user()->hasAccess('USERS', 'E'))
                                <a href="{{ route('users.edit', $user->id) }}" 
                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                
                                @if(auth()->user()->hasAccess('USERS', 'D') && $user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.delete', $user->id) }}" 
                                      class="d-inline" onsubmit="return confirm('Delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-users fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No users found</p>
                            @if(auth()->user()->hasAccess('USERS', 'A'))
                            <a href="{{ route('users.add') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add First User
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .datatable {
        font-size: 0.875rem;
    }
    .btn-group .btn {
        margin-right: 0.25rem;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush 