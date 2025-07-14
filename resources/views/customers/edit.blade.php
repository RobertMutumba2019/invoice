@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="container-fluid">
    <div class="block-header">
        <div class="row">
            <div class="col">
                <h2>Edit Customer</h2>
                <small class="text-muted">Update customer information</small>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Customer Information - {{ $customer->business_name }}</h2>
                </div>
                <div class="body">
                    <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_code">Customer Code</label>
                                    <input type="text" id="customer_code" class="form-control" value="{{ $customer->customer_code }}" readonly>
                                    <small class="text-muted">Customer code cannot be changed</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="business_name">Business Name <span class="text-danger">*</span></label>
                                    <input type="text" name="business_name" id="business_name" class="form-control" value="{{ old('business_name', $customer->business_name) }}" required>
                                    @error('business_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_person">Contact Person</label>
                                    <input type="text" name="contact_person" id="contact_person" class="form-control" value="{{ old('contact_person', $customer->contact_person) }}">
                                    @error('contact_person')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $customer->email) }}">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                                    @error('phone')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mobile">Mobile</label>
                                    <input type="text" name="mobile" id="mobile" class="form-control" value="{{ old('mobile', $customer->mobile) }}">
                                    @error('mobile')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tin_number">TIN Number</label>
                                    <input type="text" name="tin_number" id="tin_number" class="form-control" value="{{ old('tin_number', $customer->tin_number) }}">
                                    @error('tin_number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vrn_number">VRN Number</label>
                                    <input type="text" name="vrn_number" id="vrn_number" class="form-control" value="{{ old('vrn_number', $customer->vrn_number) }}">
                                    @error('vrn_number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_type">Customer Type <span class="text-danger">*</span></label>
                                    <select name="customer_type" id="customer_type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="INDIVIDUAL" {{ old('customer_type', $customer->customer_type) == 'INDIVIDUAL' ? 'selected' : '' }}>Individual</option>
                                        <option value="COMPANY" {{ old('customer_type', $customer->customer_type) == 'COMPANY' ? 'selected' : '' }}>Company</option>
                                        <option value="GOVERNMENT" {{ old('customer_type', $customer->customer_type) == 'GOVERNMENT' ? 'selected' : '' }}>Government</option>
                                        <option value="NGO" {{ old('customer_type', $customer->customer_type) == 'NGO' ? 'selected' : '' }}>NGO</option>
                                    </select>
                                    @error('customer_type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_category">Customer Category <span class="text-danger">*</span></label>
                                    <select name="customer_category" id="customer_category" class="form-control" required>
                                        <option value="">Select Category</option>
                                        <option value="REGULAR" {{ old('customer_category', $customer->customer_category) == 'REGULAR' ? 'selected' : '' }}>Regular</option>
                                        <option value="WHOLESALE" {{ old('customer_category', $customer->customer_category) == 'WHOLESALE' ? 'selected' : '' }}>Wholesale</option>
                                        <option value="RETAIL" {{ old('customer_category', $customer->customer_category) == 'RETAIL' ? 'selected' : '' }}>Retail</option>
                                        <option value="EXPORT" {{ old('customer_category', $customer->customer_category) == 'EXPORT' ? 'selected' : '' }}>Export</option>
                                        <option value="VIP" {{ old('customer_category', $customer->customer_category) == 'VIP' ? 'selected' : '' }}>VIP</option>
                                    </select>
                                    @error('customer_category')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="credit_limit">Credit Limit</label>
                                    <input type="number" name="credit_limit" id="credit_limit" class="form-control" value="{{ old('credit_limit', $customer->credit_limit) }}" step="0.01" min="0">
                                    @error('credit_limit')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_terms">Payment Terms (Days)</label>
                                    <input type="number" name="payment_terms" id="payment_terms" class="form-control" value="{{ old('payment_terms', $customer->payment_terms) }}" min="0">
                                    @error('payment_terms')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text" name="country" id="country" class="form-control" value="{{ old('country', $customer->country) }}">
                                    @error('country')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $customer->city) }}">
                                    @error('city')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="postal_code">Postal Code</label>
                                    <input type="text" name="postal_code" id="postal_code" class="form-control" value="{{ old('postal_code', $customer->postal_code) }}">
                                    @error('postal_code')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">Status</label>
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $customer->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea name="address" id="address" class="form-control" rows="3">{{ old('address', $customer->address) }}</textarea>
                                    @error('address')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bank_name">Bank Name</label>
                                    <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ old('bank_name', $customer->bank_name) }}">
                                    @error('bank_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bank_account">Bank Account</label>
                                    <input type="text" name="bank_account" id="bank_account" class="form-control" value="{{ old('bank_account', $customer->bank_account) }}">
                                    @error('bank_account')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bank_branch">Bank Branch</label>
                                    <input type="text" name="bank_branch" id="bank_branch" class="form-control" value="{{ old('bank_branch', $customer->bank_branch) }}">
                                    @error('bank_branch')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $customer->notes) }}</textarea>
                                    @error('notes')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="zmdi zmdi-save"></i> Update Customer
                                </button>
                                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-secondary">
                                    <i class="zmdi zmdi-arrow-left"></i> Back to Customer
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