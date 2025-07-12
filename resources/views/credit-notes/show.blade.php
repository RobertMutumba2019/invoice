@extends('layouts.app')

@section('title', 'Credit Note Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('credit-notes.index') }}">Credit Notes</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
                <h4 class="page-title">Credit Note Details</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Credit Note #{{ $creditNote->cn_no }}</h5>
                        <div>
                            <a href="{{ route('credit-notes.print', $creditNote->cn_id) }}" class="btn btn-info"><i class="mdi mdi-printer"></i> Print</a>
                            @if($creditNote->status === 'DRAFT')
                                <a href="{{ route('credit-notes.edit', $creditNote->cn_id) }}" class="btn btn-warning">Edit</a>
                                <form method="POST" action="{{ route('credit-notes.submit-efris', $creditNote->cn_id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Submit this credit note to EFRIS?')">Submit to EFRIS</button>
                                </form>
                                <form method="POST" action="{{ route('credit-notes.cancel', $creditNote->cn_id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this credit note?')">Cancel</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($creditNote->status == 'DRAFT')
                                <span class="badge bg-warning">Draft</span>
                            @elseif($creditNote->status == 'SUBMITTED')
                                <span class="badge bg-info">Submitted</span>
                            @elseif($creditNote->status == 'APPROVED')
                                <span class="badge bg-success">Approved</span>
                            @elseif($creditNote->status == 'CANCELLED')
                                <span class="badge bg-danger">Cancelled</span>
                            @else
                                <span class="badge bg-secondary">{{ $creditNote->status }}</span>
                            @endif
                        </dd>
                        <dt class="col-sm-4">Original Invoice</dt>
                        <dd class="col-sm-8">{{ $creditNote->original_invoice_no }}</dd>
                        <dt class="col-sm-4">Buyer</dt>
                        <dd class="col-sm-8">{{ $creditNote->buyer_name }}</dd>
                        <dt class="col-sm-4">Reason</dt>
                        <dd class="col-sm-8">{{ $creditNote->reason }}</dd>
                        <dt class="col-sm-4">Reason Code</dt>
                        <dd class="col-sm-8">{{ $creditNote->reason_code }}</dd>
                        <dt class="col-sm-4">Amount</dt>
                        <dd class="col-sm-8">{{ number_format($creditNote->total_amount, 2) }} {{ $creditNote->currency }}</dd>
                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8">{{ $creditNote->created_at->format('M d, Y H:i') }}</dd>
                    </dl>
                    <hr>
                    <h5>Items</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Tax</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($creditNote->items as $item)
                                <tr>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->unit_price, 2) }}</td>
                                    <td>{{ number_format($item->total_amount, 2) }}</td>
                                    <td>{{ number_format($item->tax_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 