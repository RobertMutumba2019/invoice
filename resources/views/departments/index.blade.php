@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-building me-2"></i>
        Departments
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @if(auth()->user()->hasAccess('DEPARTMENTS', 'A'))
        <a href="{{ route('departments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Department
        </a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Description</th>
                        <th>Added By</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                    <tr>
                        <td>{{ $department->dept_name }}</td>
                        <td>{{ $department->dept_description ?: 'No description' }}</td>
                        <td>{{ $department->addedBy->full_name ?? 'System' }}</td>
                        <td>{{ $department->dept_date_added->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                @if(auth()->user()->hasAccess('DEPARTMENTS', 'E'))
                                <a href="{{ route('departments.edit', $department->dept_id) }}" 
                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                
                                @if(auth()->user()->hasAccess('DEPARTMENTS', 'D'))
                                <form method="POST" action="{{ route('departments.destroy', $department->dept_id) }}" 
                                      class="d-inline" onsubmit="return confirm('Delete this department?')">
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
                        <td colspan="5" class="text-center py-4">
                            <i class="fas fa-building fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No departments found</p>
                            @if(auth()->user()->hasAccess('DEPARTMENTS', 'A'))
                            <a href="{{ route('departments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add First Department
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($departments->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $departments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection 