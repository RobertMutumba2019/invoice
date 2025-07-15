@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-edit me-2"></i>
        Edit User: {{ $user->user_surname }} {{ $user->user_othername }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Users
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_surname" class="form-label">Surname *</label>
                        <input type="text" class="form-control @error('user_surname') is-invalid @enderror" 
                               id="user_surname" name="user_surname" 
                               value="{{ old('user_surname', $user->user_surname) }}" required>
                        @error('user_surname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_othername" class="form-label">Other Names</label>
                        <input type="text" class="form-control @error('user_othername') is-invalid @enderror" 
                               id="user_othername" name="user_othername" 
                               value="{{ old('user_othername', $user->user_othername) }}">
                        @error('user_othername')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_name" class="form-label">Username *</label>
                        <input type="text" class="form-control @error('user_name') is-invalid @enderror" 
                               id="user_name" name="user_name" 
                               value="{{ old('user_name', $user->user_name) }}" required>
                        @error('user_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" 
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password (leave blank to keep current)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control @error('user_phone') is-invalid @enderror" 
                               id="user_phone" name="user_phone" 
                               value="{{ old('user_phone', $user->user_phone) }}">
                        @error('user_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_department_id" class="form-label">Department</label>
                        <select class="form-select @error('user_department_id') is-invalid @enderror" 
                                id="user_department_id" name="user_department_id">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->dept_id }}" 
                                        {{ old('user_department_id', $user->user_department_id) == $department->dept_id ? 'selected' : '' }}>
                                    {{ $department->dept_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_designation" class="form-label">Designation</label>
                        <select class="form-select @error('user_designation') is-invalid @enderror" 
                                id="user_designation" name="user_designation">
                            <option value="">Select Designation</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->designation_id }}" 
                                        {{ old('user_designation', $user->user_designation) == $designation->designation_id ? 'selected' : '' }}>
                                    {{ $designation->designation_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_designation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="roles" class="form-label">Roles</label>
                        <select class="form-select select2 @error('roles') is-invalid @enderror" 
                                id="roles" name="roles[]" multiple>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" 
                                        {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('roles')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="user_active" name="user_active" value="1"
                                   {{ old('user_active', $user->user_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="user_active">
                                Active User
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select options...'
    });
});
</script>
@endpush 