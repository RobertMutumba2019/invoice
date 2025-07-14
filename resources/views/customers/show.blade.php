@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="container-fluid">
    <div class="block-header">
        <div class="row">
            <div class="col">
                <h2>Customer Details</h2>
                <small class="text-muted">View customer information and statistics</small>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>{{ $customer->business_name }}</h2>
                    <ul class="header-dropdown">
                        <li>
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning">
                                <i class="zmdi zmdi-edit"></i> Edit
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customers.statement', $customer->id) }}" class="btn btn-info">
                                <i class="zmdi zmdi-receipt"></i> Statement
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                <i class="zmdi zmdi-arrow-left"></i> Back to Customers
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row">
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
                                    <th>Phone:</th>
                                    <td>{{ $customer->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Mobile:</th>
                                    <td>{{ $customer->mobile ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>TIN Number:</th>
                                    <td>{{ $customer->tin_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>VRN Number:</th>
                                    <td>{{ $customer->vrn_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Customer Type:</th>
                                    <td>{{ $customer->customerTypeName }}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ $customer->customerCategoryName }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Status:</th>
                                    <td>{!! $customer->status_badge !!}</td>
                                </tr>
                                <tr>
                                    <th>Credit Limit:</th>
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
                                    <th>Country:</th>
                                    <td>{{ $customer->country }}</td>
                                </tr>
                                <tr>
                                    <th>City:</th>
                                    <td>{{ $customer->city ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Postal Code:</th>
                                    <td>{{ $customer->postal_code ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>{{ $customer->creator->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created Date:</th>
                                    <td>{{ $customer->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($customer->address)
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Address:</h5>
                                <p>{{ $customer->address }}</p>
                            </div>
                        </div>
                    @endif

                    @if($customer->bank_name || $customer->bank_account)
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Bank Information:</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="150">Bank Name:</th>
                                        <td>{{ $customer->bank_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Account Number:</th>
                                        <td>{{ $customer->bank_account ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Branch:</th>
                                        <td>{{ $customer->bank_branch ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if($customer->notes)
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Notes:</h5>
                                <p>{{ $customer->notes }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Customer Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4>Customer Statistics</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="body text-center">
                                            <h3>{{ $stats['total_invoices'] }}</h3>
                                            <p>Total Invoices</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="body text-center">
                                            <h3>{{ number_format($stats['total_amount'], 2) }}</h3>
                                            <p>Total Amount</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="body text-center">
                                            <h3>{{ number_format($stats['paid_amount'], 2) }}</h3>
                                            <p>Paid Amount</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="body text-center">
                                            <h3>{{ number_format($stats['pending_amount'], 2) }}</h3>
                                            <p>Pending Amount</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Invoices -->
                    @if($customer->invoices->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4>Recent Invoices</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Invoice No</th>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($customer->invoices->take(5) as $invoice)
                                                <tr>
                                                    <td>{{ $invoice->invoice_no }}</td>
                                                    <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                                    <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                                    <td><span class="badge badge-{{ $invoice->status_badge_class }}">{{ $invoice->status }}</span></td>
                                                    <td>
                                                        <a href="{{ route('invoices.show', $invoice->invoice_id) }}" class="btn btn-sm btn-info">View</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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