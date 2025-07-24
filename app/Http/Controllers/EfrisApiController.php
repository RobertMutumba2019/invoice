<?php

namespace App\Http\Controllers;

use App\Services\EfrisService;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EfrisApiController extends Controller
{
    protected $efrisService;

    public function __construct(EfrisService $efrisService)
    {
        $this->efrisService = $efrisService;
    }

    /**
     * Display EFRIS settings page.
     */
    public function settings()
    {
        $settings = SystemSetting::getEfrisSettings();
        $settingsArray = [];
        
        foreach ($settings as $setting) {
            $settingsArray[$setting->setting_key] = $setting->setting_value;
        }

        return view('efris.settings', compact('settingsArray'));
    }

    /**
     * Update EFRIS settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'efris_api_url' => 'required|url',
            'efris_tin' => 'required|string|max:20',
            'efris_business_name' => 'required|string|max:255',
            'efris_device_number' => 'required|string|max:50',
            'efris_device_mac' => 'required|string|max:50',
            'efris_latitude' => 'required|numeric|between:-90,90',
            'efris_longitude' => 'required|numeric|between:-180,180',
            'efris_default_currency' => 'required|string|max:3',
            'efris_vat_rate' => 'required|numeric|min:0|max:100',
        ]);

        try {
            // Update settings
            SystemSetting::setValue('efris_api_url', $request->efris_api_url, 'string', 'efris', 'EFRIS API URL', 'Base URL for EFRIS API endpoints');
            SystemSetting::setValue('efris_tin', $request->efris_tin, 'string', 'efris', 'EFRIS TIN', 'Tax Identification Number for EFRIS');
            SystemSetting::setValue('efris_business_name', $request->efris_business_name, 'string', 'efris', 'Business Name', 'Official business name for EFRIS');
            SystemSetting::setValue('efris_device_number', $request->efris_device_number, 'string', 'efris', 'Device Number', 'EFRIS device registration number');
            SystemSetting::setValue('efris_device_mac', $request->efris_device_mac, 'string', 'efris', 'Device MAC', 'EFRIS device MAC address');
            SystemSetting::setValue('efris_latitude', $request->efris_latitude, 'float', 'efris', 'Latitude', 'Business location latitude');
            SystemSetting::setValue('efris_longitude', $request->efris_longitude, 'float', 'efris', 'Longitude', 'Business location longitude');
            SystemSetting::setValue('efris_default_currency', $request->efris_default_currency, 'string', 'efris', 'Default Currency', 'Default currency for invoices');
            SystemSetting::setValue('efris_vat_rate', $request->efris_vat_rate, 'float', 'efris', 'VAT Rate', 'Default VAT rate percentage');

            // Clear cache
            SystemSetting::clearCache();

            return redirect()->route('efris.settings')
                ->with('success', 'EFRIS settings updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update EFRIS settings: ' . $e->getMessage());
            return redirect()->route('efris.settings')
                ->with('error', 'Failed to update EFRIS settings: ' . $e->getMessage());
        }
    }

    /**
     * Test EFRIS API connection.
     */
    public function testConnection()
    {
        try {
            $result = $this->efrisService->testConnection();
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'EFRIS API connection successful',
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'EFRIS API connection failed',
                    'data' => $result
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('EFRIS API test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'EFRIS API test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get EFRIS API status.
     */
    public function getStatus()
    {
        try {
            // Get config directly from SystemSetting to avoid service dependency issues
            $config = [
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
            
            // Test connection using a simple HTTP request
            $connectionTest = ['success' => false, 'message' => 'Connection test not available'];
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get($config['api_url']);
                $connectionTest = [
                    'success' => $response->successful(),
                    'message' => $response->successful() ? 'Connection successful' : 'Connection failed'
                ];
            } catch (\Exception $e) {
                $connectionTest = [
                    'success' => false,
                    'message' => 'Connection failed: ' . $e->getMessage()
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'config' => $config,
                    'connection' => $connectionTest,
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get EFRIS status: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get EFRIS status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate EFRIS configuration.
     */
    public function validateConfig()
    {
        try {
            $config = $this->efrisService->getEfrisConfig();
            $errors = [];
            
            // Validate required fields
            if (empty($config['api_url'])) {
                $errors[] = 'EFRIS API URL is required';
            }
            
            if (empty($config['tin'])) {
                $errors[] = 'EFRIS TIN is required';
            }
            
            if (empty($config['business_name'])) {
                $errors[] = 'Business name is required';
            }
            
            if (empty($config['device_number'])) {
                $errors[] = 'Device number is required';
            }
            
            if (empty($config['device_mac'])) {
                $errors[] = 'Device MAC is required';
            }
            
            if (empty($config['latitude']) || empty($config['longitude'])) {
                $errors[] = 'Location coordinates are required';
            }
            
            if (empty($config['currency'])) {
                $errors[] = 'Default currency is required';
            }
            
            if ($config['vat_rate'] < 0 || $config['vat_rate'] > 100) {
                $errors[] = 'VAT rate must be between 0 and 100';
            }
            
            if (empty($errors)) {
                return response()->json([
                    'success' => true,
                    'message' => 'EFRIS configuration is valid',
                    'data' => $config
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'EFRIS configuration has errors',
                    'errors' => $errors
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to validate EFRIS config: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate EFRIS config: ' . $e->getMessage()
            ], 500);
        }
    }

    // Fetch Invoices
   
    public function getInvoiceStatus(Request $request)
{
    $invoiceNo = $request->input('invoice_no');
    if (!$invoiceNo) {
        return back()->with('error', 'Invoice number is required');
    }

    $efrisService = new \App\Services\EfrisService();
    $result = $efrisService->fetchInvoiceStatus($invoiceNo);

    if ($result['success']) {
        return view('search_invoice', ['invoice' => $result['invoice']]);
    } else {
        return back()->with('error', $result['message'] ?? 'Unknown error');
    }
}

    /**
     * Get EFRIS API logs.
     */
    public function getLogs(Request $request)
    {
        try {
            $limit = $request->get('limit', 100);
            $logFile = storage_path('logs/laravel.log');
            
            if (!file_exists($logFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log file not found'
                ], 404);
            }
            
            $logs = [];
            $lines = file($logFile);
            $efrisLines = [];
            
            // Get last N lines and filter for EFRIS related logs
            $recentLines = array_slice($lines, -$limit);
            
            foreach ($recentLines as $line) {
                if (strpos($line, 'EFRIS') !== false || strpos($line, 'efris') !== false) {
                    $efrisLines[] = $line;
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'logs' => array_slice($efrisLines, -50), // Return last 50 EFRIS logs
                    'total_lines' => count($efrisLines),
                    'log_file' => $logFile
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get EFRIS logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get EFRIS logs: ' . $e->getMessage()
            ], 500);
        }
    }
} 