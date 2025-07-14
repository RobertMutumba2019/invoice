<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EfrisService;
use App\Models\SystemSetting;

class TestEfrisApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'efris:test {--config : Test configuration only} {--connection : Test connection only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test EFRIS API integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Testing EFRIS API Integration...');
        $this->newLine();

        $efrisService = app(EfrisService::class);

        // Test configuration
        if ($this->option('config') || !$this->option('connection')) {
            $this->testConfiguration($efrisService);
        }

        // Test connection
        if ($this->option('connection') || !$this->option('config')) {
            $this->testConnection($efrisService);
        }

        $this->newLine();
        $this->info('✅ EFRIS API test completed!');
    }

    /**
     * Test EFRIS configuration.
     */
    private function testConfiguration($efrisService)
    {
        $this->info('📋 Testing Configuration...');
        
        try {
            $config = $efrisService->getEfrisConfig();
            
            $this->table(
                ['Setting', 'Value', 'Status'],
                [
                    ['API URL', $config['api_url'], $this->getStatusIcon(!empty($config['api_url']))],
                    ['TIN', $config['tin'], $this->getStatusIcon(!empty($config['tin']))],
                    ['Business Name', $config['business_name'], $this->getStatusIcon(!empty($config['business_name']))],
                    ['Device Number', $config['device_number'], $this->getStatusIcon(!empty($config['device_number']))],
                    ['Device MAC', $config['device_mac'], $this->getStatusIcon(!empty($config['device_mac']))],
                    ['Latitude', $config['latitude'], $this->getStatusIcon(!empty($config['latitude']))],
                    ['Longitude', $config['longitude'], $this->getStatusIcon(!empty($config['longitude']))],
                    ['Currency', $config['currency'], $this->getStatusIcon(!empty($config['currency']))],
                    ['VAT Rate', $config['vat_rate'] . '%', $this->getStatusIcon($config['vat_rate'] >= 0 && $config['vat_rate'] <= 100)],
                ]
            );
            
            $this->info('✅ Configuration test completed successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Configuration test failed: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    /**
     * Test EFRIS connection.
     */
    private function testConnection($efrisService)
    {
        $this->info('🔌 Testing Connection...');
        
        try {
            $result = $efrisService->testConnection();
            
            if ($result['success']) {
                $this->info('✅ Connection successful!');
                $this->line('Message: ' . $result['message']);
            } else {
                $this->error('❌ Connection failed!');
                $this->line('Message: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error('❌ Connection test failed: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    /**
     * Get status icon.
     */
    private function getStatusIcon($status)
    {
        return $status ? '✅' : '❌';
    }
} 