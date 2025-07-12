<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .buyer-info, .invoice-info {
            width: 45%;
        }
        .invoice-info {
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .totals {
            text-align: right;
            margin-top: 20px;
        }
        .total-row {
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">EFRIS INVOICE SYSTEM</div>
        <div>Electronic Fiscal Receipting and Invoicing Solution</div>
    </div>

    <div class="invoice-details">
        <div class="buyer-info">
            <strong>Bill To:</strong><br>
            {{ $invoice->buyer_name }}<br>
            @if($invoice->buyer_tin)
                TIN: {{ $invoice->buyer_tin }}<br>
            @endif
            @if($invoice->buyer_address)
                {{ $invoice->buyer_address }}<br>
            @endif
            @if($invoice->buyer_phone)
                Phone: {{ $invoice->buyer_phone }}<br>
            @endif
            @if($invoice->buyer_email)
                Email: {{ $invoice->buyer_email }}
            @endif
        </div>
        <div class="invoice-info">
            <strong>Invoice No:</strong> {{ $invoice->invoice_no }}<br>
            @if($invoice->efris_invoice_no)
                <strong>EFRIS No:</strong> {{ $invoice->efris_invoice_no }}<br>
            @endif
            <strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}<br>
            <strong>Type:</strong> {{ $invoice->invoice_type }}<br>
            <strong>Status:</strong> {{ $invoice->status }}<br>
            <strong>Currency:</strong> {{ $invoice->currency }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Code</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Tax Rate</th>
                <th>Tax Amount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->good->eg_name }}</td>
                <td>{{ $item->good->eg_code }}</td>
                <td>{{ number_format($item->quantity, 2) }} {{ $item->good->eg_uom }}</td>
                <td>{{ number_format($item->unit_price) }}</td>
                <td>{{ $item->tax_rate }}%</td>
                <td>{{ number_format($item->tax_amount) }}</td>
                <td>{{ number_format($item->total_amount) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div><strong>Subtotal:</strong> {{ number_format($invoice->invoice_amount) }} {{ $invoice->currency }}</div>
        <div><strong>Tax Amount:</strong> {{ number_format($invoice->tax_amount) }} {{ $invoice->currency }}</div>
        <div class="total-row"><strong>Total Amount:</strong> {{ number_format($invoice->total_amount) }} {{ $invoice->currency }}</div>
    </div>

    @if($invoice->remarks)
    <div style="margin-top: 20px;">
        <strong>Remarks:</strong><br>
        {{ $invoice->remarks }}
    </div>
    @endif

    <div class="footer">
        <p>Generated on {{ now()->format('M d, Y H:i:s') }} by {{ $invoice->creator->full_name }}</p>
        <p>This is a computer generated document. No signature required.</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()">Print Invoice</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html> 