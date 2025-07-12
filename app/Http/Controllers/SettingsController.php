<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Display the settings index page.
     */
    public function index()
    {
        $groups = [
            'efris' => 'EFRIS Configuration',
            'email' => 'Email Settings',
            'system' => 'System Settings',
        ];

        $settings = [];
        foreach ($groups as $group => $label) {
            $settings[$group] = SystemSetting::getByGroup($group);
        }

        return view('settings.index', compact('settings', 'groups'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validator = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        try {
            foreach ($validator['settings'] as $key => $value) {
                if ($value !== null) {
                    $setting = SystemSetting::where('setting_key', $key)->first();
                    if ($setting) {
                        $setting->update(['setting_value' => $value]);
                        Cache::forget("setting.{$key}");
                    }
                }
            }

            return redirect()->route('settings.index')
                ->with('success', 'Settings updated successfully');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update settings: ' . $e->getMessage()]);
        }
    }

    /**
     * Test EFRIS API connection.
     */
    public function testEfrisConnection()
    {
        try {
            $efrisService = app(\App\Services\EfrisService::class);
            $result = $efrisService->testConnection();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'EFRIS API connection successful'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'EFRIS API connection failed: ' . $result['message']
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Clear system cache.
     */
    public function clearCache()
    {
        try {
            SystemSetting::clearCache();
            Cache::flush();

            return redirect()->route('settings.index')
                ->with('success', 'System cache cleared successfully');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to clear cache: ' . $e->getMessage()]);
        }
    }

    /**
     * Export settings.
     */
    public function export()
    {
        $settings = SystemSetting::all();
        
        $filename = 'system_settings_' . date('Y-m-d_H-i-s') . '.json';
        
        return response()->streamDownload(function () use ($settings) {
            echo json_encode($settings->toArray(), JSON_PRETTY_PRINT);
        }, $filename);
    }

    /**
     * Import settings.
     */
    public function import(Request $request)
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json',
        ]);

        try {
            $content = file_get_contents($request->file('settings_file')->getPathname());
            $settings = json_decode($content, true);

            foreach ($settings as $setting) {
                SystemSetting::updateOrCreate(
                    ['setting_key' => $setting['setting_key']],
                    $setting
                );
            }

            SystemSetting::clearCache();

            return redirect()->route('settings.index')
                ->with('success', 'Settings imported successfully');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to import settings: ' . $e->getMessage()]);
        }
    }
} 