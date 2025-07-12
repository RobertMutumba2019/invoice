@extends('layouts.app')

@section('title', 'Edit Department')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-edit me-2"></i>
        Edit Department
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('departments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Departments
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-building me-2"></i>
                    Edit Department Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('departments.update', $department->dept_id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="dept_name" class="form-label">Department Name *</label>
                            <input type="text" class="form-control @error('dept_name') is-invalid @enderror" 
                                   id="dept_name" name="dept_name" value="{{ old('dept_name', $department->dept_name) }}" required>
                            @error('dept_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label for="dept_description" class="form-label">Description</label>
                            <textarea class="form-control @error('dept_description') is-invalid @enderror" 
                                      id="dept_description" name="dept_description" rows="3">{{ old('dept_description', $department->dept_description) }}</textarea>
                            @error('dept_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 