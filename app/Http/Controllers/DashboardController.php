<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use App\Models\EfrisGood;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Update user's last activity
        auth()->user()->updateLastActivity();

        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get recent activities
        $recentActivities = AuditTrail::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get recent invoices
        $recentInvoices = Invoice::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get monthly invoice chart data
        $monthlyInvoices = $this->getMonthlyInvoiceData();

        return view('dashboard', compact('stats', 'recentActivities', 'recentInvoices', 'monthlyInvoices'));
    }

    /**
     * Get dashboard statistics.
     */
    private function getDashboardStats()
    {
        $currentMonth = now()->startOfMonth();
        $currentYear = now()->startOfYear();

        return [
            'total_invoices' => Invoice::count(),
            'total_invoices_month' => Invoice::whereMonth('created_at', $currentMonth->month)->count(),
            'total_invoices_year' => Invoice::whereYear('created_at', $currentYear->year)->count(),
            
            'draft_invoices' => Invoice::draft()->count(),
            'submitted_invoices' => Invoice::submitted()->count(),
            'approved_invoices' => Invoice::approved()->count(),
            
            'total_amount_month' => Invoice::whereMonth('created_at', $currentMonth->month)->sum('total_amount'),
            'total_amount_year' => Invoice::whereYear('created_at', $currentYear->year)->sum('total_amount'),
            
            'total_users' => User::count(),
            'active_users' => User::where('user_active', true)->count(),
            'online_users' => User::where('user_online', true)->count(),
            
            'total_goods' => EfrisGood::count(),
            'active_goods' => EfrisGood::active()->count(),
        ];
    }

    /**
     * Get monthly invoice data for chart.
     */
    private function getMonthlyInvoiceData()
    {
        $months = [];
        $counts = [];
        $amounts = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');
            
            $monthCount = Invoice::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
                
            $monthAmount = Invoice::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');

            $months[] = $monthName;
            $counts[] = $monthCount;
            $amounts[] = $monthAmount;
        }

        return [
            'months' => $months,
            'counts' => $counts,
            'amounts' => $amounts,
        ];
    }

    /**
     * Get invoice statistics by type.
     */
    public function getInvoiceStatsByType()
    {
        $stats = DB::table('invoices')
            ->select('invoice_type', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total_amount'))
            ->groupBy('invoice_type')
            ->get();

        return response()->json($stats);
    }

    /**
     * Get invoice statistics by status.
     */
    public function getInvoiceStatsByStatus()
    {
        $stats = DB::table('invoices')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json($stats);
    }

    /**
     * Get user activity statistics.
     */
    public function getUserActivityStats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('user_active', true)->count(),
            'online_users' => User::where('user_online', true)->count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json($stats);
    }
} 