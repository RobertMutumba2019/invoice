@extends('layouts.app')

@section('title', 'Designations')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-id-badge me-2"></i>
        Designations
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @if(auth()->user()->hasAccess('DESIGNATIONS', 'A'))
        <a href="{{ route('designations.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Designation
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
                        <th>Designation Name</th>
                        <th>Description</th>
                        <th>Added By</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($designations as $designation)
                    <tr>
                        <td>{{ $designation->designation_name }}</td>
                        <td>{{ $designation->designation_description ?: 'No description' }}</td>
                        <td>{{ $designation->addedBy->full_name ?? 'System' }}</td>
                        <td>{{ $designation->designation_date_added->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                @if(auth()->user()->hasAccess('DESIGNATIONS', 'E'))
                                <a href="{{ route('designations.edit', $designation->designation_id) }}" 
                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                
                                @if(auth()->user()->hasAccess('DESIGNATIONS', 'D'))
                                <form method="POST" action="{{ route('designations.destroy', $designation->designation_id) }}" 
                                      class="d-inline" onsubmit="return confirm('Delete this designation?')">
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
                            <i class="fas fa-id-badge fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No designations found</p>
                            @if(auth()->user()->hasAccess('DESIGNATIONS', 'A'))
                            <a href="{{ route('designations.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add First Designation
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($designations->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $designations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection 