<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;

echo "Total invoices: " . Invoice::count() . "\n";
echo "Approved invoices: " . Invoice::where('status', 'APPROVED')->count() . "\n";

$invoices = Invoice::all(['invoice_id', 'invoice_no', 'buyer_name', 'status']);
foreach ($invoices as $inv) {
    echo $inv->invoice_id . " - " . $inv->invoice_no . " - " . $inv->buyer_name . " - " . $inv->status . "\n";
}

$invoice = Invoice::with(['items'])->first();
if ($invoice && $invoice->items->count()) {
    $item = $invoice->items->first();
    echo "\nFirst invoice item keys: ";
    print_r(array_keys($item->getAttributes()));
    echo "\n";
    print_r($item->getAttributes());
} 