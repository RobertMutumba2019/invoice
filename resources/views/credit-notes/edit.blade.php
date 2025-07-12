@extends('layouts.app')

@section('title', 'Edit Credit Note')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('credit-notes.index') }}">Credit Notes</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
                <h4 class="page-title">Edit Credit Note</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('credit-notes.update', $creditNote->cn_id) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason</label>
                            <textarea class="form-control" id="reason" name="reason" rows="2" required>{{ old('reason', $creditNote->reason) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="reason_code" class="form-label">Reason Code</label>
                            <input type="text" class="form-control" id="reason_code" name="reason_code" value="{{ old('reason_code', $creditNote->reason_code) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Items to Credit</label>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th>Credit Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($creditNote->items as $item)
                                    <tr>
                                        <td>{{ $item->item_name }}</td>
                                        <td>{{ $item->originalItem ? $item->originalItem->quantity : '-' }}</td>
                                        <td>{{ number_format($item->unit_price, 2) }}</td>
                                        <td>
                                            <input type="number" step="0.01" min="0.01" max="{{ $item->originalItem ? $item->originalItem->quantity : $item->quantity }}" name="items[{{ $item->cni_id }}][quantity]" class="form-control" value="{{ old('items.'.$item->cni_id.'.quantity', $item->quantity) }}" required>
                                            <input type="hidden" name="items[{{ $item->cni_id }}][item_id]" value="{{ $item->cni_id }}">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mb-3 text-end">
                            <a href="{{ route('credit-notes.show', $creditNote->cn_id) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Credit Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 