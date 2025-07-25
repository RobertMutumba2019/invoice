@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-plus me-2"></i>
        Create New Invoice
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Invoices
        </a>
    </div>
</div>

<form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm">
    @csrf
    
    <div class="row">
        <!-- Invoice Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        Invoice Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="invoice_type" class="form-label">Invoice Type *</label>
                            <select class="form-select @error('invoice_type') is-invalid @enderror" id="invoice_type" name="invoice_type" required>
                                <option value="">Select Type</option>
                                <option value="LOCAL" {{ old('invoice_type') == 'LOCAL' ? 'selected' : '' }}>Local</option>
                                <option value="EXPORT" {{ old('invoice_type') == 'EXPORT' ? 'selected' : '' }}>Export</option>
                                <option value="CONTRACT" {{ old('invoice_type') == 'CONTRACT' ? 'selected' : '' }}>Contract</option>
                                <option value="AUCTION" {{ old('invoice_type') == 'AUCTION' ? 'selected' : '' }}>Auction</option>
                            </select>
                            @error('invoice_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="invoice_date" class="form-label">Invoice Date *</label>
                            <input type="date" class="form-control @error('invoice_date') is-invalid @enderror" 
                                   id="invoice_date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                            @error('invoice_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="currency" class="form-label">Currency *</label>
                            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                <option value="">Select Currency</option>
                                <option value="UGX" {{ old('currency') == 'UGX' ? 'selected' : '' }}>UGX (Ugandan Shilling)</option>
                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP (British Pound)</option>
                            </select>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buyer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        Buyer Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="buyer_name" class="form-label">Buyer Name *</label>
                            <input type="text" class="form-control @error('buyer_name') is-invalid @enderror" 
                                   id="buyer_name" name="buyer_name" value="{{ old('buyer_name') }}" required>
                            @error('buyer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="buyer_tin" class="form-label">Buyer TIN</label>
                            <input type="text" class="form-control @error('buyer_tin') is-invalid @enderror" 
                                   id="buyer_tin" name="buyer_tin" value="{{ old('buyer_tin') }}">
                            @error('buyer_tin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="buyer_nin_brn" class="form-label">Buyer NIN/BRN</label>
                            <input type="text" class="form-control @error('buyer_nin_brn') is-invalid @enderror" 
                                   id="buyer_nin_brn" name="buyer_nin_brn" value="{{ old('buyer_nin_brn') }}">
                            @error('buyer_nin_brn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="buyer_phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control @error('buyer_phone') is-invalid @enderror" 
                                   id="buyer_phone" name="buyer_phone" value="{{ old('buyer_phone') }}">
                            @error('buyer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="buyer_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('buyer_email') is-invalid @enderror" 
                                   id="buyer_email" name="buyer_email" value="{{ old('buyer_email') }}">
                            @error('buyer_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="buyer_type" class="form-label">Buyer Type *</label>
                            <select class="form-select @error('buyer_type') is-invalid @enderror" id="buyer_type" name="buyer_type" required>
                                <option value="">Select Buyer Type</option>
                                <option value="0" {{ old('buyer_type') == '0' ? 'selected' : '' }}>B2B</option>
                                <option value="1" {{ old('buyer_type') == '1' ? 'selected' : '' }}>B2C</option>
                            </select>
                            @error('buyer_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label for="buyer_address" class="form-label">Address</label>
                            <textarea class="form-control @error('buyer_address') is-invalid @enderror" 
                                      id="buyer_address" name="buyer_address" rows="3">{{ old('buyer_address') }}</textarea>
                            @error('buyer_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Invoice Items
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                        <i class="fas fa-plus me-1"></i>Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div id="items-container">
                        <!-- Items will be added here dynamically -->
                    </div>
                    <div class="text-center mt-3" id="no-items-message">
                        <p class="text-muted">No items added yet. Click "Add Item" to start.</p>
                    </div>
                </div>
            </div>

            <!-- Remarks -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comment me-2"></i>
                        Remarks
                    </h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control @error('remarks') is-invalid @enderror" 
                              id="remarks" name="remarks" rows="3" placeholder="Additional notes or remarks...">{{ old('remarks') }}</textarea>
                    @error('remarks')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator me-2"></i>
                        Invoice Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6">Subtotal:</div>
                        <div class="col-6 text-end" id="subtotal">0.00</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">Tax Amount:</div>
                        <div class="col-6 text-end" id="tax-amount">0.00</div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-6"><strong>Total:</strong></div>
                        <div class="col-6 text-end"><strong id="total-amount">0.00</strong></div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create Invoice
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Item Template (Hidden) -->
<template id="item-template">
    <div class="item-row border rounded p-3 mb-3" data-item-index="">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Item *</label>
                <select class="form-select item-select" name="items[INDEX][good_id]" required>
                    <option value="">Select Item</option>
                    @foreach($goods as $good)
                        <option value="{{ $good->eg_id }}" 
                                data-price="{{ $good->eg_price }}" 
                                data-tax-rate="{{ $good->eg_tax_rate }}"
                                data-uom="{{ $good->eg_uom }}">
                            {{ $good->eg_name }} ({{ $good->eg_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity *</label>
                <input type="number" class="form-control item-quantity" 
                       name="items[INDEX][quantity]" step="0.01" min="0.01" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Unit Price *</label>
                <input type="number" class="form-control item-price" 
                       name="items[INDEX][unit_price]" step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tax Rate</label>
                <input type="number" class="form-control item-tax-rate" 
                       name="items[INDEX][tax_rate]" step="0.01" min="0" max="100" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">Total</label>
                <input type="text" class="form-control item-total" readonly>
                <button type="button" class="btn btn-danger btn-sm mt-1" onclick="removeItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
let itemIndex = 0;

function addItem() {
    const container = document.getElementById('items-container');
    const template = document.getElementById('item-template');
    const noItemsMessage = document.getElementById('no-items-message');
    
    // Hide no items message
    noItemsMessage.style.display = 'none';
    
    // Clone template
    const itemRow = template.content.cloneNode(true);
    const itemDiv = itemRow.querySelector('.item-row');
    
    // Set item index
    itemDiv.setAttribute('data-item-index', itemIndex);
    
    // Update form field names
    itemDiv.querySelectorAll('[name*="INDEX"]').forEach(field => {
        field.name = field.name.replace('INDEX', itemIndex);
    });
    
    // Add event listeners
    const select = itemDiv.querySelector('.item-select');
    const quantity = itemDiv.querySelector('.item-quantity');
    const price = itemDiv.querySelector('.item-price');
    const taxRate = itemDiv.querySelector('.item-tax-rate');
    
    select.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            price.value = option.dataset.price;
            taxRate.value = option.dataset.taxRate;
            calculateItemTotal(itemDiv);
        }
    });
    
    quantity.addEventListener('input', () => calculateItemTotal(itemDiv));
    price.addEventListener('input', () => calculateItemTotal(itemDiv));
    taxRate.addEventListener('input', () => calculateItemTotal(itemDiv));
    
    container.appendChild(itemDiv);
    itemIndex++;
}

function removeItem(button) {
    const itemRow = button.closest('.item-row');
    itemRow.remove();
    
    // Show no items message if no items left
    const container = document.getElementById('items-container');
    if (container.children.length === 0) {
        document.getElementById('no-items-message').style.display = 'block';
    }
    
    calculateTotals();
}

function calculateItemTotal(itemDiv) {
    const quantity = parseFloat(itemDiv.querySelector('.item-quantity').value) || 0;
    const price = parseFloat(itemDiv.querySelector('.item-price').value) || 0;
    const taxRate = parseFloat(itemDiv.querySelector('.item-tax-rate').value) || 0;
    
    const subtotal = quantity * price;
    const taxAmount = subtotal * (taxRate / 100);
    const total = subtotal + taxAmount;
    
    itemDiv.querySelector('.item-total').value = total.toFixed(2);
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let taxAmount = 0;
    
    document.querySelectorAll('.item-row').forEach(itemDiv => {
        const quantity = parseFloat(itemDiv.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(itemDiv.querySelector('.item-price').value) || 0;
        const taxRate = parseFloat(itemDiv.querySelector('.item-tax-rate').value) || 0;
        
        const itemSubtotal = quantity * price;
        const itemTaxAmount = itemSubtotal * (taxRate / 100);
        
        subtotal += itemSubtotal;
        taxAmount += itemTaxAmount;
    });
    
    const total = subtotal + taxAmount;
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('tax-amount').textContent = taxAmount.toFixed(2);
    document.getElementById('total-amount').textContent = total.toFixed(2);
}

// Form validation
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.item-row');
    if (items.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the invoice.');
        return false;
    }
    
    // Validate each item
    let isValid = true;
    items.forEach(itemDiv => {
        const select = itemDiv.querySelector('.item-select');
        const quantity = itemDiv.querySelector('.item-quantity');
        const price = itemDiv.querySelector('.item-price');
        
        if (!select.value || !quantity.value || !price.value) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields for each item.');
        return false;
    }
});

// Add first item on page load
document.addEventListener('DOMContentLoaded', function() {
    addItem();
});
</script>
@endpush 