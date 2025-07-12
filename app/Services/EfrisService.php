<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\EfrisGood;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EfrisService
{
    protected $latitude = "0.4061957";
    protected $longitude = "32.643798";
    protected $deviceNumber = "TCS5a2ce23154445074";
    protected $deviceMAC = "TCS2a80082879377106";
    protected $tin = "1000023516";
    protected $businessName = 'CIVIL AVIATION AUTHORITY';
    protected $apiUrl = 'https://efris.ura.go.ug/efrisapi/api/';

    /**
     * Generate invoice number.
     */
    public function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('invoice_id', 'desc')
            ->first();
        
        $sequence = $lastInvoice ? (intval(substr($lastInvoice->invoice_no, -4)) + 1) : 1;
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new invoice.
     */
    public function createInvoice($data)
    {
        $invoice = Invoice::create([
            'invoice_no' => $this->generateInvoiceNumber(),
            'buyer_tin' => $data['buyer_tin'] ?? null,
            'buyer_name' => $data['buyer_name'],
            'buyer_address' => $data['buyer_address'] ?? null,
            'buyer_phone' => $data['buyer_phone'] ?? null,
            'buyer_email' => $data['buyer_email'] ?? null,
            'currency' => $data['currency'] ?? 'UGX',
            'invoice_type' => $data['invoice_type'] ?? 'LOCAL',
            'status' => 'DRAFT',
            'remarks' => $data['remarks'] ?? null,
            'created_by' => auth()->id(),
            'invoice_date' => $data['invoice_date'] ?? now(),
            'invoice_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ]);

        // Add items to invoice
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $this->addItemToInvoice($invoice, $itemData);
            }
        }

        // Calculate totals
        $invoice->calculateTotals();

        AuditTrail::register('INVOICE_CREATED', "Invoice {$invoice->invoice_no} created", 'invoices');

        return $invoice;
    }

    /**
     * Add item to invoice.
     */
    public function addItemToInvoice($invoice, $itemData)
    {
        $good = EfrisGood::find($itemData['good_id']);
        
        if (!$good) {
            throw new \Exception('Good not found');
        }

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->invoice_id,
            'good_id' => $good->eg_id,
            'item_name' => $good->eg_name,
            'item_code' => $good->eg_code,
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'] ?? $good->eg_price,
            'uom' => $good->eg_uom,
            'tax_category' => $good->eg_tax_category,
            'tax_rate' => $good->eg_tax_rate,
            'total_amount' => 0,
            'tax_amount' => 0,
        ]);

        $item->calculateTotals();

        return $item;
    }

    /**
     * Submit invoice to EFRIS.
     */
    public function submitToEfris($invoice)
    {
        try {
            // Validate invoice before submission
            if (!$invoice->items || $invoice->items->count() === 0) {
                throw new \Exception('Invoice must have at least one item');
            }

            if (empty($invoice->buyer_name)) {
                throw new \Exception('Buyer name is required');
            }

            if ($invoice->total_amount <= 0) {
                throw new \Exception('Invoice total amount must be greater than zero');
            }

            $payload = $this->buildEfrisPayload($invoice);
            
            Log::info('Submitting invoice to EFRIS', [
                'invoice_id' => $invoice->invoice_id,
                'invoice_no' => $invoice->invoice_no,
                'api_url' => $this->apiUrl . 'submitInvoice',
                'payload' => $payload
            ]);
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->apiUrl . 'submitInvoice', $payload);

            Log::info('EFRIS API response received', [
                'invoice_id' => $invoice->invoice_id,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'response_headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('EFRIS API response parsed', [
                    'invoice_id' => $invoice->invoice_id,
                    'response_data' => $responseData
                ]);
                
                if (isset($responseData['returnStateInfo']['returnCode']) && $responseData['returnStateInfo']['returnCode'] === 'SUCCESS') {
                    $invoice->update([
                        'status' => 'SUBMITTED',
                        'efris_invoice_no' => $responseData['data']['invoiceNo'] ?? null,
                        'qr_code' => $responseData['data']['qrCode'] ?? null,
                        'fdn' => $responseData['data']['fdn'] ?? null,
                    ]);

                    AuditTrail::register('INVOICE_SUBMITTED', "Invoice {$invoice->invoice_no} submitted to EFRIS", 'invoices');
                    
                    return [
                        'success' => true,
                        'message' => 'Invoice submitted successfully',
                        'data' => $responseData
                    ];
                } else {
                    $errorMessage = $responseData['returnStateInfo']['returnMessage'] ?? 'Unknown EFRIS API error';
                    Log::error('EFRIS API returned error', [
                        'invoice_id' => $invoice->invoice_id,
                        'return_code' => $responseData['returnStateInfo']['returnCode'] ?? 'UNKNOWN',
                        'return_message' => $errorMessage,
                        'full_response' => $responseData
                    ]);
                    throw new \Exception($errorMessage);
                }
            } else {
                Log::error('EFRIS API HTTP error', [
                    'invoice_id' => $invoice->invoice_id,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'response_headers' => $response->headers()
                ]);
                throw new \Exception('Failed to connect to EFRIS API: HTTP ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('EFRIS submission failed: ' . $e->getMessage(), [
                'invoice_id' => $invoice->invoice_id,
                'invoice_no' => $invoice->invoice_no,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            AuditTrail::register('INVOICE_SUBMISSION_FAILED', "Failed to submit invoice {$invoice->invoice_no}: {$e->getMessage()}", 'invoices');

            return [
                'success' => false,
                'message' => 'Failed to submit invoice: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test EFRIS API connectivity.
     */
    public function testConnection()
    {
        try {
            $testPayload = [
                'globalInfo' => [
                    'appId' => 'AP04',
                    'brn' => '',
                    'dataExchangeId' => '9230489223014123',
                    'deviceMAC' => $this->deviceMAC,
                    'deviceNo' => $this->deviceNumber,
                    'extendField' => '@@#####@@',
                    'longitude' => $this->longitude,
                    'latitude' => $this->latitude,
                    'interfaceCode' => 'T110',
                    'requestCode' => 'TP',
                    'requestTime' => now()->format('Y-m-d H:i:s'),
                    'responseCode' => 'TA',
                    'taxpayerID' => '723542954718704352',
                    'userName' => 'admin',
                    'tin' => $this->tin,
                    'version' => '1.1.20191201'
                ],
                'returnStateInfo' => [
                    'returnCode' => '',
                    'returnMessage' => ''
                ]
            ];

            Log::info('Testing EFRIS API connection', [
                'api_url' => $this->apiUrl . 'test',
                'payload' => $testPayload
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->apiUrl . 'test', $testPayload);

            Log::info('EFRIS API test response', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'response_headers' => $response->headers()
            ]);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('EFRIS API test failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build EFRIS API payload.
     */
    protected function buildEfrisPayload($invoice)
    {
        $items = [];
        foreach ($invoice->items as $item) {
            $items[] = [
                'itemCode' => $item->item_code,
                'itemName' => $item->item_name,
                'qty' => $item->quantity,
                'unitPrice' => $item->unit_price,
                'total' => $item->total_amount,
                'tax' => $item->tax_amount,
                'orderNumber' => $invoice->invoice_no,
                'uom' => $this->convertUOM($item->uom),
                'taxRate' => $item->tax_rate,
            ];
        }

        Log::info('Building EFRIS payload', [
            'invoice_id' => $invoice->invoice_id,
            'items_count' => count($items),
            'items' => $items
        ]);

        return [
            'data' => [
                'content' => [
                    'basicInformation' => [
                        'invoiceNo' => $invoice->invoice_no,
                        'invoiceDate' => $invoice->invoice_date->format('Y-m-d H:i:s'),
                        'currency' => $invoice->currency,
                        'operator' => auth()->user()->user_name,
                    ],
                    'sellerInformation' => [
                        'tin' => $this->tin,
                        'ninBrn' => '4988',
                        'legalName' => 'UGANDA CIVIL AVIATION AUTHORITY',
                        'businessName' => $this->businessName,
                        'address' => '',
                        'mobilePhone' => '2560778497936',
                        'linePhone' => '2560778497936',
                        'emailAddress' => 'nthakkar@ura.go.ug',
                        'placeOfBusiness' => '',
                    ],
                    'buyerInformation' => [
                        'tin' => $invoice->buyer_tin,
                        'ninBrn' => '',
                        'legalName' => $invoice->buyer_name,
                        'businessName' => $invoice->buyer_name,
                        'address' => $invoice->buyer_address,
                        'mobilePhone' => $invoice->buyer_phone,
                        'linePhone' => $invoice->buyer_phone,
                        'emailAddress' => $invoice->buyer_email,
                        'placeOfBusiness' => '',
                    ],
                    'goodsDetails' => $items,
                    'summary' => [
                        'grossAmount' => $invoice->invoice_amount,
                        'taxAmount' => $invoice->tax_amount,
                        'netAmount' => $invoice->total_amount,
                        'remarks' => $invoice->remarks,
                    ],
                ],
                'signature' => '',
                'dataDescription' => [
                    'codeType' => '0',
                    'encryptCode' => '0',
                    'zipCode' => '0'
                ]
            ],
            'globalInfo' => [
                'appId' => 'AP04',
                'brn' => '',
                'dataExchangeId' => '9230489223014123',
                'deviceMAC' => $this->deviceMAC,
                'deviceNo' => $this->deviceNumber,
                'extendField' => '@@#####@@',
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'interfaceCode' => 'T110',
                'requestCode' => 'TP',
                'requestTime' => now()->format('Y-m-d H:i:s'),
                'responseCode' => 'TA',
                'taxpayerID' => '723542954718704352',
                'userName' => 'admin',
                'tin' => $this->tin,
                'version' => '1.1.20191201'
            ],
            'returnStateInfo' => [
                'returnCode' => '',
                'returnMessage' => ''
            ]
        ];
    }

    /**
     * Convert UOM to EFRIS format.
     */
    protected function convertUOM($uom)
    {
        return match(strtoupper(trim($uom))) {
            'BILLING' => '213',
            'UNIT' => 'UN',
            'PER MONTH' => '115',
            'GMS' => 'GRM',
            'KGS' => 'KGM',
            default => $uom,
        };
    }

    /**
     * Get tax category code.
     */
    protected function getTaxCategory($category)
    {
        return match(strtoupper($category)) {
            'V', '18', 'I' => '18',
            'Z', '0' => '0',
            'D' => 'D',
            'E', '-' => 'E',
            default => (string)$category,
        };
    }
} 