<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // EFRIS API Settings
            [
                'setting_key' => 'efris_api_url',
                'setting_value' => 'https://efris.ura.go.ug/efrisapi/api/',
                'setting_type' => 'string',
                'setting_group' => 'efris',
                'setting_label' => 'EFRIS API URL',
                'setting_description' => 'Base URL for EFRIS API endpoints',
            ],
            [
                'setting_key' => 'efris_tin',
                'setting_value' => '1000023516',
                'setting_type' => 'string',
                'setting_group' => 'efris',
                'setting_label' => 'Business TIN',
                'setting_description' => 'Tax Identification Number for the business',
            ],
            [
                'setting_key' => 'efris_business_name',
                'setting_value' => 'CIVIL AVIATION AUTHORITY',
                'setting_type' => 'string',
                'setting_group' => 'efris',
                'setting_label' => 'Business Name',
                'setting_description' => 'Official business name for EFRIS',
            ],
            [
                'setting_key' => 'efris_device_number',
                'setting_value' => 'TCS5a2ce23154445074',
                'setting_type' => 'string',
                'setting_group' => 'efris',
                'setting_label' => 'Device Number',
                'setting_description' => 'EFRIS device registration number',
            ],
            [
                'setting_key' => 'efris_device_mac',
                'setting_value' => 'TCS2a80082879377106',
                'setting_type' => 'string',
                'setting_group' => 'efris',
                'setting_label' => 'Device MAC',
                'setting_description' => 'EFRIS device MAC address',
            ],
            [
                'setting_key' => 'efris_latitude',
                'setting_value' => '0.4061957',
                'setting_type' => 'string',
                'setting_group' => 'efris',
                'setting_label' => 'Business Latitude',
                'setting_description' => 'Business location latitude',
            ],
            [
                'setting_key' => 'efris_longitude',
                'setting_value' => '32.643798',
                'setting_type' => 'string',
                'setting_group' => 'efris',
                'setting_label' => 'Business Longitude',
                'setting_description' => 'Business location longitude',
            ],
            [
                'setting_key' => 'efris_default_currency',
                'setting_value' => 'UGX',
                'setting_type' => 'string',
                'setting_group' => 'efris',
                'setting_label' => 'Default Currency',
                'setting_description' => 'Default currency for invoices',
            ],
            [
                'setting_key' => 'efris_vat_rate',
                'setting_value' => '18',
                'setting_type' => 'integer',
                'setting_group' => 'efris',
                'setting_label' => 'VAT Rate (%)',
                'setting_description' => 'Default VAT rate percentage',
            ],

            // Email Settings
            [
                'setting_key' => 'email_from_address',
                'setting_value' => 'noreply@efris.com',
                'setting_type' => 'string',
                'setting_group' => 'email',
                'setting_label' => 'From Email Address',
                'setting_description' => 'Default sender email address',
            ],
            [
                'setting_key' => 'email_from_name',
                'setting_value' => 'EFRIS System',
                'setting_type' => 'string',
                'setting_group' => 'email',
                'setting_label' => 'From Name',
                'setting_description' => 'Default sender name',
            ],
            [
                'setting_key' => 'email_notifications_enabled',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'setting_group' => 'email',
                'setting_label' => 'Enable Email Notifications',
                'setting_description' => 'Enable or disable email notifications',
            ],

            // System Settings
            [
                'setting_key' => 'system_name',
                'setting_value' => 'EFRIS Invoice Management System',
                'setting_type' => 'string',
                'setting_group' => 'system',
                'setting_label' => 'System Name',
                'setting_description' => 'Name of the system displayed in UI',
            ],
            [
                'setting_key' => 'system_version',
                'setting_value' => '1.0.0',
                'setting_type' => 'string',
                'setting_group' => 'system',
                'setting_label' => 'System Version',
                'setting_description' => 'Current system version',
            ],
            [
                'setting_key' => 'session_timeout',
                'setting_value' => '120',
                'setting_type' => 'integer',
                'setting_group' => 'system',
                'setting_label' => 'Session Timeout (minutes)',
                'setting_description' => 'User session timeout in minutes',
            ],
            [
                'setting_key' => 'max_login_attempts',
                'setting_value' => '5',
                'setting_type' => 'integer',
                'setting_group' => 'system',
                'setting_label' => 'Max Login Attempts',
                'setting_description' => 'Maximum failed login attempts before lockout',
            ],
            [
                'setting_key' => 'password_expiry_days',
                'setting_value' => '90',
                'setting_type' => 'integer',
                'setting_group' => 'system',
                'setting_label' => 'Password Expiry (days)',
                'setting_description' => 'Number of days before password expires',
            ],
            [
                'setting_key' => 'backup_enabled',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'setting_group' => 'system',
                'setting_label' => 'Enable Database Backup',
                'setting_description' => 'Enable automatic database backups',
            ],
            [
                'setting_key' => 'backup_frequency',
                'setting_value' => 'daily',
                'setting_type' => 'string',
                'setting_group' => 'system',
                'setting_label' => 'Backup Frequency',
                'setting_description' => 'How often to perform backups (daily, weekly, monthly)',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $setting['setting_key']],
                $setting
            );
        }
    }
} 