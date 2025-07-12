@extends('layouts.app')

@section('title', 'Add Good/Service')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-plus me-2"></i>
        Add New Good/Service
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('goods.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Goods
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-box me-2"></i>
                    Good/Service Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('goods.store') }}">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="eg_name" class="form-label">Name *</label>
                            <input type="text" class="form-control @error('eg_name') is-invalid @enderror" 
                                   id="eg_name" name="eg_name" value="{{ old('eg_name') }}" required>
                            @error('eg_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="eg_code" class="form-label">Code *</label>
                            <input type="text" class="form-control @error('eg_code') is-invalid @enderror" 
                                   id="eg_code" name="eg_code" value="{{ old('eg_code') }}" required>
                            @error('eg_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="eg_price" class="form-label">Price *</label>
                            <input type="number" class="form-control @error('eg_price') is-invalid @enderror" 
                                   id="eg_price" name="eg_price" value="{{ old('eg_price') }}" step="0.01" min="0" required>
                            @error('eg_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="eg_uom" class="form-label">Unit of Measure *</label>
                            <input type="text" class="form-control @error('eg_uom') is-invalid @enderror" 
                                   id="eg_uom" name="eg_uom" value="{{ old('eg_uom') }}" required>
                            @error('eg_uom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="eg_tax_category" class="form-label">Tax Category *</label>
                            <select class="form-select @error('eg_tax_category') is-invalid @enderror" 
                                    id="eg_tax_category" name="eg_tax_category" required>
                                <option value="">Select Category</option>
                                <option value="V" {{ old('eg_tax_category') == 'V' ? 'selected' : '' }}>VAT</option>
                                <option value="Z" {{ old('eg_tax_category') == 'Z' ? 'selected' : '' }}>Zero Rated</option>
                                <option value="E" {{ old('eg_tax_category') == 'E' ? 'selected' : '' }}>Exempt</option>
                                <option value="D" {{ old('eg_tax_category') == 'D' ? 'selected' : '' }}>Deemed</option>
                            </select>
                            @error('eg_tax_category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="eg_tax_rate" class="form-label">Tax Rate (%) *</label>
                            <input type="number" class="form-control @error('eg_tax_rate') is-invalid @enderror" 
                                   id="eg_tax_rate" name="eg_tax_rate" value="{{ old('eg_tax_rate', 18) }}" 
                                   step="0.01" min="0" max="100" required>
                            @error('eg_tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label for="eg_description" class="form-label">Description</label>
                            <textarea class="form-control @error('eg_description') is-invalid @enderror" 
                                      id="eg_description" name="eg_description" rows="3">{{ old('eg_description') }}</textarea>
                            @error('eg_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('goods.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create Good/Service
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 