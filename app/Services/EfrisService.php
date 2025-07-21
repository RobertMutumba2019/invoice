<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\EfrisGood;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SystemSetting;

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

    /**
     * Get EFRIS configuration from settings.
     */
    public function getEfrisConfig()
    {
        return [
            'api_url' => SystemSetting::getValue('efris_api_url', 'https://efris.ura.go.ug/efrisapi/api/'),
            'tin' => SystemSetting::getValue('efris_tin', '1000023516'),
            'business_name' => SystemSetting::getValue('efris_business_name', 'CIVIL AVIATION AUTHORITY'),
            'device_number' => SystemSetting::getValue('efris_device_number', 'TCS5a2ce23154445074'),
            'device_mac' => SystemSetting::getValue('efris_device_mac', 'TCS2a80082879377106'),
            'latitude' => SystemSetting::getValue('efris_latitude', '0.4061957'),
            'longitude' => SystemSetting::getValue('efris_longitude', '32.643798'),
            'currency' => SystemSetting::getValue('efris_default_currency', 'UGX'),
            'vat_rate' => SystemSetting::getValue('efris_vat_rate', 18),
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
                        'tin' => $config['tin'],
                        'ninBrn' => '4988',
                        'legalName' => 'UGANDA CIVIL AVIATION AUTHORITY',
                        'businessName' => $config['business_name'],
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
                'deviceMAC' => $config['device_mac'],
                'deviceNo' => $config['device_number'],
                'extendField' => '@@#####@@',
                'longitude' => $config['longitude'],
                'latitude' => $config['latitude'],
                'interfaceCode' => 'T110',
                'requestCode' => 'TP',
                'requestTime' => now()->format('Y-m-d H:i:s'),
                'responseCode' => 'TA',
                'taxpayerID' => '723542954718704352',
                'userName' => 'admin',
                'tin' => $config['tin'],
                'version' => '1.1.20191201'
            ],
            'returnStateInfo' => [
                'returnCode' => '',
                'returnMessage' => ''
            ]
        ];
    }

    /**
     * Submit credit note to EFRIS.
     */
    public function submitCreditNote($creditNote)
    {
        try {
            // Validate credit note before submission
            if (!$creditNote->items || $creditNote->items->count() === 0) {
                throw new \Exception('Credit note must have at least one item');
            }

            if (empty($creditNote->buyer_name)) {
                throw new \Exception('Buyer name is required');
            }

            if ($creditNote->total_amount <= 0) {
                throw new \Exception('Credit note total amount must be greater than zero');
            }

            $payload = $this->buildCreditNotePayload($creditNote);
            
            Log::info('Submitting credit note to EFRIS', [
                'credit_note_id' => $creditNote->cn_id,
                'credit_note_no' => $creditNote->cn_no,
                'api_url' => $this->apiUrl . 'submitCreditNote',
                'payload' => $payload
            ]);
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->apiUrl . 'submitCreditNote', $payload);

            Log::info('EFRIS credit note API response received', [
                'credit_note_id' => $creditNote->cn_id,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'response_headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('EFRIS credit note API response parsed', [
                    'credit_note_id' => $creditNote->cn_id,
                    'response_data' => $responseData
                ]);
                
                if (isset($responseData['returnStateInfo']['returnCode']) && $responseData['returnStateInfo']['returnCode'] === 'SUCCESS') {
                    $creditNote->update([
                        'status' => 'SUBMITTED',
                        'efris_cn_no' => $responseData['data']['creditNoteNo'] ?? null,
                        'fdn' => $responseData['data']['fdn'] ?? null,
                        'qr_code' => $responseData['data']['qrCode'] ?? null,
                        'efris_response' => $responseData,
                    ]);

                    AuditTrail::register('CREDIT_NOTE_SUBMITTED', "Credit note {$creditNote->cn_no} submitted to EFRIS", 'credit_notes');
                    
                    return [
                        'success' => true,
                        'message' => 'Credit note submitted successfully',
                        'data' => $responseData
                    ];
                } else {
                    $errorMessage = $responseData['returnStateInfo']['returnMessage'] ?? 'Unknown EFRIS API error';
                    Log::error('EFRIS credit note API returned error', [
                        'credit_note_id' => $creditNote->cn_id,
                        'return_code' => $responseData['returnStateInfo']['returnCode'] ?? 'UNKNOWN',
                        'return_message' => $errorMessage,
                        'full_response' => $responseData
                    ]);
                    throw new \Exception($errorMessage);
                }
            } else {
                Log::error('EFRIS credit note API HTTP error', [
                    'credit_note_id' => $creditNote->cn_id,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'response_headers' => $response->headers()
                ]);
                throw new \Exception('Failed to connect to EFRIS API: HTTP ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('EFRIS credit note submission failed: ' . $e->getMessage(), [
                'credit_note_id' => $creditNote->cn_id,
                'credit_note_no' => $creditNote->cn_no,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            AuditTrail::register('CREDIT_NOTE_SUBMISSION_FAILED', "Failed to submit credit note {$creditNote->cn_no}: {$e->getMessage()}", 'credit_notes');

            return [
                'success' => false,
                'message' => 'Failed to submit credit note: ' . $e->getMessage()
            ];
        }
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
} 