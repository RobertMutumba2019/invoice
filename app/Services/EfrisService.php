<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\EfrisGood;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SystemSetting;
use Carbon\Carbon;

class EfrisService
{
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

    Log::info('createInvoice input data', ['data' => $data]); // Log input for debugging

    // Validate buyer_tin for B2B transactions
    $buyerTin = $data['buyer_tin'] ?? null;
    $buyerType = isset($data['buyer_type']) ? $data['buyer_type'] : ((empty($buyerTin) || strlen($buyerTin) < 10) ? '1' : '0');

    // Explicitly validate: if B2B and buyerTin is empty, throw error
if ($buyerType === '0' && empty($buyerTin)) {
    Log::error('Invalid input: buyer_tin is empty for B2B transaction', ['data' => $data]);
    throw new \Exception('Buyer TIN is required for B2B transactions');
}


    $invoice = Invoice::create([
        'invoice_no' => $this->generateInvoiceNumber(),
        'buyer_tin' => $buyerTin,
        'buyer_name' => $data['buyer_name'] ?? 'Unknown Buyer',
        'buyer_type' => $data['buyer_type'] ?? $buyerType,
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

    Log::info('Invoice created', [
        'invoice_id' => $invoice->invoice_id,
        'buyer_tin' => $invoice->buyer_tin,
        'buyer_type' => $invoice->buyer_type
    ]);

    AuditTrail::register('INVOICE_CREATED', "Invoice {$invoice->invoice_no} created", 'invoices');

    return $invoice;
}

    /**
     * Add an item to an invoice.
     */
    public function addItemToInvoice(Invoice $invoice, array $itemData)
    {
        $good = EfrisGood::find($itemData['good_id']);

        $item = new InvoiceItem([
            'invoice_id' => $invoice->invoice_id, // Ensure invoice_id is set
            'good_id' => $itemData['good_id'],
            'item_name' => $good ? $good->eg_name : ($itemData['item_name'] ?? 'Unknown'),
            'item_code' => $good ? $good->eg_code : ($itemData['item_code'] ?? ''),
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'],
            'tax_rate' => $itemData['tax_rate'] ?? ($good->eg_tax_rate ?? 0),
            'uom' => $good ? $good->eg_uom : ($itemData['uom'] ?? ''),
            'tax_category' => $good ? $good->eg_tax_category : ($itemData['tax_category'] ?? 'V'),
        ]);
        $item->calculateTotals();
        $item->save(); // Save directly
    }

    /**
     * Submit invoice to EFRIS.
     */
    public function submitToEfris($invoice)
    {
        try {
            $config = $this->getEfrisConfig();

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
            Log::info('EFRIS payload', ['payload' => $payload]);
            $jsonPayload = json_encode($payload);
            Log::info('Submitting invoice to EFRIS', [
                'invoice_id' => $invoice->invoice_id,
                'invoice_no' => $invoice->invoice_no,
                'api_url' => $config['api_url'],
                'payload' => $jsonPayload
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->withBody($jsonPayload, 'application/json')->post($config['api_url']);

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


    public function getEfrisConfig()
{
    return [
        'api_url' => SystemSetting::getValue('efris_api_url', 'http://127.0.0.1:9880/efristcs/ws/tcsapp/getInformation'),
        'tin' => SystemSetting::getValue('efris_tin', '1000023516'),
        'business_name' => SystemSetting::getValue('efris_business_name', 'CIVIL AVIATION AUTHORITY'),
        'address' => SystemSetting::getValue('efris_address', 'Default Address'),
        'mobile_phone' => SystemSetting::getValue('efris_mobile_phone', '2560778497936'),
        'line_phone' => SystemSetting::getValue('efris_line_phone', '2560778497936'),
        'email_address' => SystemSetting::getValue('efris_email_address', 'nthakkar@ura.go.ug'),
        'place_of_business' => SystemSetting::getValue('efris_place_of_business', 'Default Location'),
        'device_number' => SystemSetting::getValue('efris_device_number', 'TCS8bb22b734414482'),
        'device_mac' => SystemSetting::getValue('efris_device_mac', 'TCS2a80082879377106'),
        'latitude' => SystemSetting::getValue('efris_latitude', '0.4061957'),
        'longitude' => SystemSetting::getValue('efris_longitude', '32.643798'),
        'currency' => SystemSetting::getValue('efris_default_currency', 'UGX'),
        'vat_rate' => SystemSetting::getValue('efris_vat_rate', 18),
        'invoice_type' => SystemSetting::getValue('efris_invoice_type', '1'),
        'invoice_industry_code' => SystemSetting::getValue('efris_invoice_industry_code', '101'),
        'antifake_code' => SystemSetting::getValue('efris_antifake_code', '12345678901234567890'),
        'mode_code' => SystemSetting::getValue('efris_mode_code', '0'),
        'qr_code' => SystemSetting::getValue('efris_qr_code', 'example.qrcode'),
        'payment_mode' => SystemSetting::getValue('efris_payment_mode', '102'),
        'operation_type' => SystemSetting::getValue('efris_operation_type', '101'),
        'nin_brn' => SystemSetting::getValue('efris_nin_brn', '4988'),
        'buyer_type' => SystemSetting::getValue('efris_buyer_type', '1'),
        'buyer_tin' => SystemSetting::getValue('efris_buyer_tin', '1000023516'),
        'non_resident_flag' => SystemSetting::getValue('efris_non_resident_flag', '0'),
        'invoice_kind' => SystemSetting::getValue('efris_invoice_kind', '1'),
        'is_batch' => SystemSetting::getValue('efris_is_batch', '0'),
    ];
}
    /**
     * Test EFRIS API connection.
     */
    public function testConnection()
    {
        try {
            $config = $this->getEfrisConfig();

            // Make a simple API call to test connection
            $response = Http::timeout(30)->get($config['api_url'] . 'test');

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Connection successful'];
            } else {
                return ['success' => false, 'message' => 'API returned status: ' . $response->status()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Build EFRIS API payload.
     */



    protected function buildEfrisPayload($invoice)
{
    $config = $this->getEfrisConfig();

    // Generate a unique reference number
    $generatedReferenceNo = 'TCS' . str_replace('-', '', (string) \Str::uuid()) . substr((string) microtime(true), -4);

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

    $requestTime = Carbon::now('Africa/Nairobi')->format('Y-m-d H:i:s');
    $issuedDate = Carbon::now('Africa/Nairobi')->format('Y-m-d H:i:s');

    // Use invoice->buyer_type, fallback to config
    $buyerType = $invoice->buyer_type ?? ($config['buyer_type'] ?? '0');
    $buyerTin = $invoice->buyer_tin;

    // For B2C, use a dummy TIN if required by EFRIS (verify with URA documentation)
    if ($buyerType === '1' && empty($buyerTin)) {
        $buyerTin = '9999999999'; // Dummy TIN for B2C, adjust if needed
    }

    // Validate buyerTin for B2B
    if ($buyerType === '0' && empty($buyerTin)) {
        Log::error('Invalid payload: buyerTin is empty for B2B transaction', [
            'invoice_id' => $invoice->invoice_id,
            'buyer_tin' => $invoice->buyer_tin,
            'buyer_type' => $buyerType
        ]);
        throw new \Exception('buyerTin cannot be empty for B2B transactions');
    }
    // Validate B2C: at least one of buyerTin, buyerNinBrn, buyerLegalName, buyerMobilePhone
    if ($buyerType === '1' && empty($buyerTin) && empty($invoice->buyer_nin_brn) && empty($invoice->buyer_name) && empty($invoice->buyer_phone)) {
        Log::error('Invalid payload: For B2C, all buyerTin, buyerNinBrn, buyerLegalName, and buyerMobilePhone are empty', [
            'invoice_id' => $invoice->invoice_id,
            'buyer_tin' => $buyerTin,
            'buyer_nin_brn' => $invoice->buyer_nin_brn,
            'buyer_name' => $invoice->buyer_name,
            'buyer_phone' => $invoice->buyer_phone
        ]);
        throw new \Exception('For B2C, at least one of buyerTin, buyerNinBrn, buyerLegalName, or buyerMobilePhone must be provided.');
    }

    $contentArray = [
        'basicInformation' => [
            'invoiceNo' => $invoice->invoice_no,
            'issuedDate' => $issuedDate,
            'currency' => $invoice->currency,
            'operator' => auth()->user()->user_name,
            'invoiceDate' => $invoice->invoice_date->format('Y-m-d H:i:s'),
            'invoiceKind' => $config['invoice_kind'] ?? '1',
            'deviceNo' => $config['device_number'],
            'invoiceType' => $config['invoice_type'] ?? '1',
            'invoiceIndustryCode' => $config['invoice_industry_code'] ?? '101',
            'isBatch' => $config['is_batch'] ?? '0',
            'antifakeCode' => $config['antifake_code'] ?? '12345678901234567890',
        ],
        'sellerDetails' => [
            'tin' => $config['tin'],
            'referenceNo' => $generatedReferenceNo,
            'ninBrn' => $config['nin_brn'] ?? '4988',
            'legalName' => 'UGANDA CIVIL AVIATION AUTHORITY',
            'businessName' => $config['business_name'],
            'address' => $config['address'] ?? 'Default Address',
            'mobilePhone' => $config['mobile_phone'] ?? '2560778497936',
            'linePhone' => $config['line_phone'] ?? '2560778497936',
            'emailAddress' => $config['email_address'] ?? 'nthakkar@ura.go.ug',
            'placeOfBusiness' => $config['place_of_business'] ?? 'Default Location',
        ],
        'buyerDetails' => [
            'tin' => $buyerTin ?? '',
            'ninBrn' => $invoice->buyer_nin_brn ?? '',
            'legalName' => $invoice->buyer_name ?? 'Unknown Buyer',
            'businessName' => $invoice->buyer_name ?? 'Unknown Buyer',
            'address' => $invoice->buyer_address ?? 'No Address Provided',
            'mobilePhone' => $invoice->buyer_phone ?? 'Unknown',
            'linePhone' => $invoice->buyer_phone ?? 'Unknown',
            'emailAddress' => $invoice->buyer_email ?? 'no-email@default.com',
            'placeOfBusiness' => $invoice->buyer_place_of_business ?? 'Unknown',
            'buyerCitizenship' => $invoice->buyer_citizenship ?? 'UG-Uganda',
            'buyerPlaceOfBusi' => $invoice->buyer_place_of_business ?? 'Unknown',
            'buyerType' => $buyerType,
            'nonResidentFlag' => $config['non_resident_flag'] ?? '0',
        ],
        'goodsDetails' => $items,
        'summary' => [
            'grossAmount' => $invoice->invoice_amount,
            'taxAmount' => $invoice->tax_amount,
            'netAmount' => $invoice->total_amount,
            'remarks' => $invoice->remarks,
            'itemCount' => count($items),
            'modeCode' => $config['mode_code'] ?? '0',
            'qrCode' => $config['qr_code'] ?? 'example.qrcode',
        ],
        'taxDetails' => [
            [
                'taxCategory' => 'Standard',
                'netAmount' => $invoice->total_amount - $invoice->tax_amount,
                'taxRate' => $invoice->items->first()->tax_rate ?? 0.18,
                'taxAmount' => $invoice->tax_amount,
                'grossAmount' => $invoice->invoice_amount,
                'taxCategoryCode' => '01',
            ],
        ],
        'payWay' => [
            [
                'paymentMode' => $config['payment_mode'] ?? '102',
                'paymentAmount' => $invoice->invoice_amount,
                'orderNumber' => 'a',
            ],
        ],
        'operationType' => $config['operation_type'] ?? '101',
        'supplierTin' => $config['tin'],
        'supplierName' => $config['business_name'],
    ];

    $base64Content = base64_encode(json_encode($contentArray));
    Log::info('EFRIS Payload Content', [
        'invoice_id' => $invoice->invoice_id,
        'buyer_tin' => $buyerTin,
        'buyer_type' => $buyerType,
        'content_array' => $contentArray,
        'base64_content' => $base64Content
    ]);

    return [
        'data' => [
            'content' => $base64Content,
            'signature' => 'DUMMY_SIGNATURE',
            'dataDescription' => [
                'codeType' => '1',
                'encryptCode' => '1',
                'zipCode' => '0'
            ]
        ],
        'globalInfo' => [
            'appId' => 'AP04',
            'version' => '1.1.20191201',
            'dataExchangeId' => str_replace('-', '', (string) \Str::uuid()),
            'interfaceCode' => 'T109',
            'requestCode' => 'TP',
            'requestTime' => $requestTime,
            'responseCode' => 'TA',
            'userName' => 'admin',
            'deviceMAC' => $config['device_mac'],
            'deviceNo' => $config['device_number'],
            'tin' => $config['tin'],
            'brn' => '',
            'taxpayerID' => '1',
            'longitude' => $config['longitude'],
            'latitude' => $config['latitude'],
            'agentType' => '0',
            'extendField' => new \StdClass(),
        ],
        'returnStateInfo' => [
            'returnCode' => '',
            'returnMessage' => ''
        ]
    ];
}



    /**
     * Build EFRIS credit note API payload.
     */
    protected function buildCreditNotePayload($creditNote)
    {
        $items = [];
        foreach ($creditNote->items as $item) {
            $items[] = [
                'itemCode' => $item->item_code,
                'itemName' => $item->item_name,
                'qty' => $item->quantity,
                'unitPrice' => $item->unit_price,
                'total' => $item->total_amount,
                'tax' => $item->tax_amount,
                'orderNumber' => $creditNote->cn_no,
                'uom' => $this->convertUOM($item->uom),
                'taxRate' => $item->tax_rate,
            ];
        }

        return [
            'data' => [
                'content' => [
                    'basicInformation' => [
                        'creditNoteNo' => $creditNote->cn_no,
                        'originalInvoiceNo' => $creditNote->original_invoice_no,
                        'creditNoteDate' => $creditNote->created_at->format('Y-m-d H:i:s'),
                        'currency' => $creditNote->currency,
                        'operator' => auth()->user()->user_name,
                        'reasonCode' => $creditNote->reason_code,
                        'reason' => $creditNote->reason,
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
                        'tin' => $creditNote->buyer_tin,
                        'ninBrn' => '',
                        'legalName' => $creditNote->buyer_name,
                        'businessName' => $creditNote->buyer_name,
                        'address' => $creditNote->buyer_address,
                        'mobilePhone' => $creditNote->buyer_phone,
                        'linePhone' => $creditNote->buyer_phone,
                        'emailAddress' => $creditNote->buyer_email,
                        'placeOfBusiness' => '',
                    ],
                    'goodsDetails' => $items,
                    'summary' => [
                        'grossAmount' => $creditNote->invoice_amount,
                        'taxAmount' => $creditNote->tax_amount,
                        'netAmount' => $creditNote->total_amount,
                        'remarks' => $creditNote->reason,
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
                'requestTime' => Carbon::now('Africa/Nairobi')->format('Y-m-d H:i:s'),
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

    /**
     * Push stock increase to EFRIS
     */
    public function pushStockToEfris($stock)
    {
        try {
            Log::info('Pushing stock increase to EFRIS', [
                'stock_id' => $stock->id,
                'item_code' => $stock->item_code,
                'quantity' => $stock->quantity
            ]);

            $payload = $this->buildStockPayload($stock);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->apiUrl . 'submitStock', $payload);

            Log::info('EFRIS stock API response received', [
                'stock_id' => $stock->id,
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                if (isset($responseData['returnStateInfo']['returnCode']) && $responseData['returnStateInfo']['returnCode'] === 'SUCCESS') {
                    AuditTrail::register('STOCK_PUSHED', "Stock increase {$stock->id} pushed to EFRIS", 'stocks');

                    return [
                        'success' => true,
                        'message' => 'Stock pushed successfully',
                        'reference' => $responseData['data']['referenceNo'] ?? null,
                        'data' => $responseData
                    ];
                } else {
                    $errorMessage = $responseData['returnStateInfo']['returnMessage'] ?? 'Unknown EFRIS API error';
                    throw new \Exception($errorMessage);
                }
            } else {
                throw new \Exception('Failed to connect to EFRIS API: HTTP ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('EFRIS stock push failed: ' . $e->getMessage(), [
                'stock_id' => $stock->id,
                'error' => $e->getMessage()
            ]);

            AuditTrail::register('STOCK_PUSH_FAILED', "Failed to push stock {$stock->id}: {$e->getMessage()}", 'stocks');

            return [
                'success' => false,
                'message' => 'Failed to push stock: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Push stock decrease to EFRIS
     */
    public function pushStockDecreaseToEfris($stockDecrease)
    {
        try {
            Log::info('Pushing stock decrease to EFRIS', [
                'stock_decrease_id' => $stockDecrease->id,
                'item_code' => $stockDecrease->item_code,
                'quantity' => $stockDecrease->quantity
            ]);

            $payload = $this->buildStockDecreasePayload($stockDecrease);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->apiUrl . 'submitStockDecrease', $payload);

            Log::info('EFRIS stock decrease API response received', [
                'stock_decrease_id' => $stockDecrease->id,
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                if (isset($responseData['returnStateInfo']['returnCode']) && $responseData['returnStateInfo']['returnCode'] === 'SUCCESS') {
                    AuditTrail::register('STOCK_DECREASE_PUSHED', "Stock decrease {$stockDecrease->id} pushed to EFRIS", 'stock_decreases');

                    return [
                        'success' => true,
                        'message' => 'Stock decrease pushed successfully',
                        'reference' => $responseData['data']['referenceNo'] ?? null,
                        'data' => $responseData
                    ];
                } else {
                    $errorMessage = $responseData['returnStateInfo']['returnMessage'] ?? 'Unknown EFRIS API error';
                    throw new \Exception($errorMessage);
                }
            } else {
                throw new \Exception('Failed to connect to EFRIS API: HTTP ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('EFRIS stock decrease push failed: ' . $e->getMessage(), [
                'stock_decrease_id' => $stockDecrease->id,
                'error' => $e->getMessage()
            ]);

            AuditTrail::register('STOCK_DECREASE_PUSH_FAILED', "Failed to push stock decrease {$stockDecrease->id}: {$e->getMessage()}", 'stock_decreases');

            return [
                'success' => false,
                'message' => 'Failed to push stock decrease: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check stock quantity via EFRIS API
     */
    public function checkStockQuantity($itemCode)
    {
        try {
            Log::info('Checking stock quantity via EFRIS', [
                'item_code' => $itemCode
            ]);

            $payload = [
                'data' => [
                    'content' => [
                        'id' => $itemCode
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
                    'interfaceCode' => 'T128',
                    'requestCode' => 'TP',
                    'requestTime' => Carbon::now('Africa/Nairobi')->format('Y-m-d H:i:s'),


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

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->apiUrl . 'getStockQuantity', $payload);

            if ($response->successful()) {
                $responseData = $response->json();

                if (isset($responseData['returnStateInfo']['returnCode']) && $responseData['returnStateInfo']['returnCode'] === 'SUCCESS') {
                    $content = base64_decode($responseData['data']['content']);
                    $stockData = json_decode($content, true);

                    return [
                        'success' => true,
                        'quantity' => $stockData['stock'] ?? 0,
                        'warning' => $stockData['stockPrewarning'] ?? false
                    ];
                } else {
                    $errorMessage = $responseData['returnStateInfo']['returnMessage'] ?? 'Unknown EFRIS API error';
                    throw new \Exception($errorMessage);
                }
            } else {
                throw new \Exception('Failed to connect to EFRIS API: HTTP ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('EFRIS stock quantity check failed: ' . $e->getMessage(), [
                'item_code' => $itemCode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to check stock quantity: ' . $e->getMessage(),
                'quantity' => 0,
                'warning' => false
            ];
        }
    }

    /**
     * Build stock increase payload
     */
    protected function buildStockPayload($stock)
    {
        return [
            'data' => [
                'content' => [
                    'goodsStockIn' => [
                        'stockInDate' => $stock->created_at->format('Y-m-d'),
                        'stockInType' => '1',
                        'stockInReason' => $stock->remarks ?? 'Stock increase',
                        'operator' => auth()->user()->user_name,
                    ],
                    'goodsStockInItem' => [
                        [
                            'itemCode' => $stock->item_code,
                            'itemName' => $stock->good->name ?? '',
                            'qty' => $stock->quantity,
                            'unitPrice' => 0,
                            'uom' => 'KGM',
                        ]
                    ]
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
                'interfaceCode' => 'T131',
                'requestCode' => 'TP',
                'requestTime' => Carbon::now('Africa/Nairobi')->format('Y-m-d H:i:s'),


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
     * Build stock decrease payload
     */
    protected function buildStockDecreasePayload($stockDecrease)
    {
        return [
            'data' => [
                'content' => [
                    'goodsStockOut' => [
                        'stockOutDate' => $stockDecrease->created_at->format('Y-m-d'),
                        'stockOutType' => '1',
                        'stockOutReason' => $stockDecrease->decrease_reason,
                        'operator' => auth()->user()->user_name,
                    ],
                    'goodsStockOutItem' => [
                        [
                            'itemCode' => $stockDecrease->item_code,
                            'itemName' => $stockDecrease->good->name ?? '',
                            'qty' => $stockDecrease->quantity,
                            'unitPrice' => 0,
                            'uom' => 'KGM',
                        ]
                    ]
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
                'interfaceCode' => 'T132',
                'requestCode' => 'TP',
                'requestTime' => Carbon::now('Africa/Nairobi')->format('Y-m-d H:i:s'),


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
}
