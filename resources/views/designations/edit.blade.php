@extends('layouts.app')

@section('title', 'Edit Designation')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-edit me-2"></i>
        Edit Designation
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('designations.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Designations
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-id-badge me-2"></i>
                    Edit Designation Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('designations.update', $designation->designation_id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="designation_name" class="form-label">Designation Name *</label>
                            <input type="text" class="form-control @error('designation_name') is-invalid @enderror" 
                                   id="designation_name" name="designation_name" value="{{ old('designation_name', $designation->designation_name) }}" required>
                            @error('designation_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label for="designation_description" class="form-label">Description</label>
                            <textarea class="form-control @error('designation_description') is-invalid @enderror" 
                                      id="designation_description" name="designation_description" rows="3">{{ old('designation_description', $designation->designation_description) }}</textarea>
                            @error('designation_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('designations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Designation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 