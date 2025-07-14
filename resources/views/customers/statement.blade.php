@extends('layouts.app')

@section('title', 'Customer Statement')

@section('content')
<div class="container-fluid">
    <div class="block-header">
        <div class="row">
            <div class="col">
                <h2>Customer Statement</h2>
                <small class="text-muted">View customer transaction history and balances</small>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Statement for {{ $customer->business_name }}</h2>
                    <ul class="header-dropdown">
                        <li>
                            <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-secondary">
                                <i class="zmdi zmdi-arrow-left"></i> Back to Customer
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="body">
                    <!-- Customer Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Customer Code:</th>
                                    <td>{{ $customer->customer_code }}</td>
                                </tr>
                                <tr>
                                    <th>Business Name:</th>
                                    <td>{{ $customer->business_name }}</td>
                                </tr>
                                <tr>
                                    <th>Contact Person:</th>
                                    <td>{{ $customer->contact_person ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $customer->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>TIN Number:</th>
                                    <td>{{ $customer->tin_number ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Credit Limit:</th>
                                    <td>{{ $customer->formatted_credit_limit }}</td>
                                </tr>
                                <tr>
                                    <th>Current Balance:</th>
                                    <td>{{ $customer->formatted_current_balance }}</td>
                                </tr>
                                <tr>
                                    <th>Available Credit:</th>
                                    <td>{!! $customer->credit_status_badge !!}</td>
                                </tr>
                                <tr>
                                    <th>Payment Terms:</th>
                                    <td>{{ $customer->payment_terms }} days</td>
                                </tr>
                                <tr>
                                    <th>Statement Date:</th>
                                    <td>{{ now()->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Transaction History -->
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Transaction History</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Reference</th>
                                            <th>Description</th>
                                            <th class="text-right">Debit</th>
                                            <th class="text-right">Credit</th>
                                            <th class="text-right">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $runningBalance = 0;
                                            $allTransactions = collect();
                                            
                                            // Add invoices
                                            foreach($invoices as $invoice) {
                                                $allTransactions->push([
                                                    'date' => $invoice->invoice_date,
                                                    'type' => 'Invoice',
                                                    'reference' => $invoice->invoice_no,
                                                    'description' => 'Invoice for goods/services',
                                                    'debit' => $invoice->total_amount,
                                                    'credit' => 0,
                                                    'balance' => 0
                                                ]);
                                            }
                                            
                                            // Add credit notes
                                            foreach($creditNotes as $creditNote) {
                                                $allTransactions->push([
                                                    'date' => $creditNote->created_at,
                                                    'type' => 'Credit Note',
                                                    'reference' => $creditNote->cn_no,
                                                    'description' => 'Credit note adjustment',
                                                    'debit' => 0,
                                                    'credit' => $creditNote->total_amount,
                                                    'balance' => 0
                                                ]);
                                            }
                                            
                                            // Sort by date
                                            $allTransactions = $allTransactions->sortBy('date');
                                        @endphp
                                        
                                        @forelse($allTransactions as $transaction)
                                            @php
                                                $runningBalance += $transaction['debit'] - $transaction['credit'];
                                                $transaction['balance'] = $runningBalance;
                                            @endphp
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}</td>
                                                <td>
                                                    @if($transaction['type'] == 'Invoice')
                                                        <span class="badge badge-primary">{{ $transaction['type'] }}</span>
                                                    @else
                                                        <span class="badge badge-success">{{ $transaction['type'] }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $transaction['reference'] }}</td>
                                                <td>{{ $transaction['description'] }}</td>
                                                <td class="text-right">
                                                    @if($transaction['debit'] > 0)
                                                        {{ number_format($transaction['debit'], 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    @if($transaction['credit'] > 0)
                                                        {{ number_format($transaction['credit'], 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($transaction['balance'], 2) }}</strong>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No transactions found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    @if($allTransactions->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="header">
                                        <h4>Summary</h4>
                                    </div>
                                    <div class="body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h5>Total Invoices</h5>
                                                    <h3 class="text-primary">{{ $invoices->count() }}</h3>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h5>Total Invoice Amount</h5>
                                                    <h3 class="text-success">{{ number_format($invoices->sum('total_amount'), 2) }}</h3>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h5>Total Credit Notes</h5>
                                                    <h3 class="text-info">{{ $creditNotes->count() }}</h3>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h5>Total Credit Amount</h5>
                                                    <h3 class="text-warning">{{ number_format($creditNotes->sum('total_amount'), 2) }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 