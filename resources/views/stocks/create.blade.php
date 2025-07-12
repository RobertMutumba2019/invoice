@extends('layouts.app')

@section('title', 'Add New Stock')

@section('content')
<div class="container-fluid">
    <div class="block-header">
        <div class="row">
            <div class="col">
                <h2>Add New Stock</h2>
                <small class="text-muted">Create a new stock entry</small>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Stock Information</h2>
                </div>
                <div class="body">
                    <form action="{{ route('stocks.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="item_code">Item Code <span class="text-danger">*</span></label>
                                    <select name="item_code" id="item_code" class="form-control" required>
                                        <option value="">Select Item</option>
                                        @foreach($goods as $good)
                                            <option value="{{ $good->eg_code }}" {{ old('item_code') == $good->eg_code ? 'selected' : '' }}>
                                                {{ $good->eg_code }} - {{ $good->eg_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('item_code')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" id="quantity" class="form-control" 
                                           value="{{ old('quantity') }}" step="0.01" min="0.01" required>
                                    @error('quantity')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sun_reference">SUN Reference</label>
                                    <input type="text" name="sun_reference" id="sun_reference" class="form-control" 
                                           value="{{ old('sun_reference') }}" placeholder="Optional SUN reference">
                                    @error('sun_reference')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea name="remarks" id="remarks" class="form-control" rows="3" 
                                              placeholder="Additional notes or remarks">{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="zmdi zmdi-save"></i> Create Stock Entry
                                </button>
                                <a href="{{ route('stocks.all') }}" class="btn btn-secondary">
                                    <i class="zmdi zmdi-arrow-left"></i> Back to Stock List
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

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize select2 for better item selection
    $('#item_code').select2({
        placeholder: 'Select an item',
        allowClear: true
    });
});
</script>
@endsection 