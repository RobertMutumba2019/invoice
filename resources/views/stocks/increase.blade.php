@extends('layouts.app')

@section('title', 'Edit Stock')

@section('content')
<div class="container-fluid">
    <div class="block-header">
        <div class="row">
            <div class="col">
                <h2>Edit Stock Entry</h2>
                <small class="text-muted">Update stock entry information</small>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Stock Entry #{{ $stock->id }}</h2>
                </div>
                <div class="body">
                    <form action="{{ route('stocks.update', $stock->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="item_code">Item Code</label>
                                    <input type="text" name="item_code" id="item_code" class="form-control" 
                                           value="{{ $stock->item_code }}" readonly>
                                    <small class="text-muted">Item code cannot be changed</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="item_name">Item Name</label>
                                    <input type="text" id="item_name" class="form-control" 
                                           value="{{ $stock->good->eg_name ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" id="quantity" class="form-control" 
                                           value="{{ old('quantity', $stock->quantity) }}" step="0.01" min="0.01" required>
                                    @error('quantity')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sun_reference">SUN Reference</label>
                                    <input type="text" name="sun_reference" id="sun_reference" class="form-control" 
                                           value="{{ old('sun_reference', $stock->sun_reference) }}" readonly>
                                    <small class="text-muted">SUN reference cannot be changed</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea name="remarks" id="remarks" class="form-control" rows="3" 
                                              placeholder="Additional notes or remarks">{{ old('remarks', $stock->remarks) }}</textarea>
                                    @error('remarks')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="zmdi zmdi-info"></i>
                                    <strong>Note:</strong> After updating, this stock entry will be submitted to EFRIS for approval.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="zmdi zmdi-save"></i> Update and Submit to EFRIS
                                </button>
                                <a href="{{ route('stocks.show', $stock->id) }}" class="btn btn-secondary">
                                    <i class="zmdi zmdi-arrow-left"></i> Back to Details
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 