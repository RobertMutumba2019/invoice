<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use App\Models\EfrisGood;
use App\Models\AuditTrail;
use App\Models\CreditNote;
use App\Models\Stock;
use App\Models\StockDecrease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get individual variables for backward compatibility
        $totalInvoices = Invoice::count();
        $totalCreditNotes = CreditNote::count();
        $totalGoods = EfrisGood::count();
        $totalUsers = User::count();
        $totalCustomers = Customer::count();
        $totalStock = Stock::count();
        $totalStockDecreases = StockDecrease::count();
        
        // Get recent activities
        $recentInvoices = Invoice::with(['creator'])->latest()->take(5)->get();
        $recentCreditNotes = CreditNote::with(['creator'])->latest()->take(5)->get();
        $recentCustomers = Customer::with(['creator'])->latest()->take(5)->get();
        $recentActivities = AuditTrail::with(['user'])->latest()->take(10)->get();

        // Get customer statistics
        $customerStats = [
            'total' => Customer::count(),
            'active' => Customer::active()->count(),
            'inactive' => Customer::inactive()->count(),
            'over_limit' => Customer::whereRaw('current_balance >= credit_limit')->count(),
            'near_limit' => Customer::whereRaw('current_balance >= credit_limit * 0.8 AND current_balance < credit_limit')->count(),
            'customers_by_type' => [
                'individual' => Customer::byType('INDIVIDUAL')->count(),
                'company' => Customer::byType('COMPANY')->count(),
                'government' => Customer::byType('GOVERNMENT')->count(),
                'ngo' => Customer::byType('NGO')->count(),
            ],
            'customers_by_category' => [
                'regular' => Customer::byCategory('REGULAR')->count(),
                'wholesale' => Customer::byCategory('WHOLESALE')->count(),
                'retail' => Customer::byCategory('RETAIL')->count(),
                'export' => Customer::byCategory('EXPORT')->count(),
                'vip' => Customer::byCategory('VIP')->count(),
            ],
        ];

        // Get monthly chart data
        $chartData = $this->getMonthlyInvoiceData();
        $monthlyInvoices = $this->getMonthlyInvoiceData();

        return view('dashboard', compact(
            'stats',
            'totalInvoices', 
            'totalCreditNotes', 
            'totalGoods', 
            'totalUsers',
            'totalCustomers',
            'totalStock',
            'totalStockDecreases',
            'recentInvoices',
            'recentCreditNotes',
            'recentCustomers',
            'recentActivities',
            'customerStats',
            'chartData',
            'monthlyInvoices'
        ));
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
            
            'total_credit_notes' => CreditNote::count(),
            'draft_credit_notes' => CreditNote::draft()->count(),
            'submitted_credit_notes' => CreditNote::submitted()->count(),
            'approved_credit_notes' => CreditNote::approved()->count(),
            
            'total_stocks' => Stock::count(),
            'pending_stocks' => Stock::pending()->count(),
            'approved_stocks' => Stock::approved()->count(),
            'total_stock_decreases' => StockDecrease::count(),
            'pending_stock_decreases' => StockDecrease::pending()->count(),
            'approved_stock_decreases' => StockDecrease::approved()->count(),
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
            'inactive_users' => User::where('user_active', false)->count(),
            'recent_logins' => User::where('user_last_changed', '>=', now()->subDays(7))->count(),
        ];

        return response()->json($stats);
    }

    public function getCustomerStats()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::active()->count(),
            'inactive_customers' => Customer::inactive()->count(),
            'over_limit_customers' => Customer::whereRaw('current_balance >= credit_limit')->count(),
            'near_limit_customers' => Customer::whereRaw('current_balance >= credit_limit * 0.8 AND current_balance < credit_limit')->count(),
            'customers_by_type' => [
                'individual' => Customer::byType('INDIVIDUAL')->count(),
                'company' => Customer::byType('COMPANY')->count(),
                'government' => Customer::byType('GOVERNMENT')->count(),
                'ngo' => Customer::byType('NGO')->count(),
            ],
            'customers_by_category' => [
                'regular' => Customer::byCategory('REGULAR')->count(),
                'wholesale' => Customer::byCategory('WHOLESALE')->count(),
                'retail' => Customer::byCategory('RETAIL')->count(),
                'export' => Customer::byCategory('EXPORT')->count(),
                'vip' => Customer::byCategory('VIP')->count(),
            ],
        ];

        return response()->json($stats);
    }
} 