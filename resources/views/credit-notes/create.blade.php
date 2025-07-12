@extends('layouts.app')

@section('title', 'Create Credit Note')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('credit-notes.index') }}">Credit Notes</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
                <h4 class="page-title">Create Credit Note</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('credit-notes.store') }}" id="creditNoteForm">
                        @csrf
                        <div class="mb-3">
                            <label for="original_invoice_id" class="form-label">Select Invoice</label>
                            <select class="form-select" id="original_invoice_id" name="original_invoice_id" required>
                                <option value="">-- Select Invoice --</option>
                                @foreach(App\Models\Invoice::orderBy('invoice_no')->get() as $inv)
                                    <option value="{{ $inv->invoice_id }}" {{ old('original_invoice_id') == $inv->invoice_id ? 'selected' : '' }}>
                                        {{ $inv->invoice_no }} - {{ $inv->buyer_name }} ({{ $inv->invoice_date->format('Y-m-d') }}) - {{ $inv->status }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Only approved invoices should be used for credit notes. Draft invoices may not be eligible for EFRIS submission.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason</label>
                            <textarea class="form-control" id="reason" name="reason" rows="2" required>{{ old('reason') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="reason_code" class="form-label">Reason Code</label>
                            <input type="text" class="form-control" id="reason_code" name="reason_code" value="{{ old('reason_code') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Items to Credit</label>
                            <div id="items-container">
                                <!-- Items will be loaded here via AJAX -->
                            </div>
                        </div>
                        <div class="mb-3 text-end">
                            <a href="{{ route('credit-notes.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Credit Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    function loadInvoiceItems(invoiceId) {
        if (!invoiceId) {
            $('#items-container').html('<div class="alert alert-info">Select an invoice to load items.</div>');
            return;
        }
        $('#items-container').html('<div class="text-center"><span class="spinner-border"></span> Loading items...</div>');
        $.get("{{ url('credit-notes/invoice') }}/" + invoiceId + "/items", function(items) {
            let html = '<table class="table table-bordered"><thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Credit Qty</th></tr></thead><tbody>';
            items.forEach(function(item) {
                html += '<tr>' +
                    '<td>' + item.item_name + '</td>' +
                    '<td>' + item.quantity + '</td>' +
                    '<td>' + item.unit_price + '</td>' +
                    '<td><input type="number" step="0.01" min="0.01" max="' + item.quantity + '" name="items[' + item.id + '][quantity]" class="form-control" value="' + item.quantity + '" required>' +
                    '<input type="hidden" name="items[' + item.id + '][item_id]" value="' + item.id + '"></td>' +
                    '</tr>';
            });
            html += '</tbody></table>';
            $('#items-container').html(html);
        });
    }
    $('#original_invoice_id').change(function() {
        loadInvoiceItems($(this).val());
    });
    // Load items if invoice is preselected
    if ($('#original_invoice_id').val()) {
        loadInvoiceItems($('#original_invoice_id').val());
    }
});
</script>
@endpush 