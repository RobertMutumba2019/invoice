<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the reports index.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Display invoice reports.
     */
    public function invoices(Request $request)
    {
        $query = Invoice::with(['creator', 'items']);

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('invoice_type', $request->type);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->get();

        $summary = [
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'total_tax' => $invoices->sum('tax_amount'),
            'draft_count' => $invoices->where('status', 'DRAFT')->count(),
            'submitted_count' => $invoices->where('status', 'SUBMITTED')->count(),
            'approved_count' => $invoices->where('status', 'APPROVED')->count(),
        ];

        return view('reports.invoices', compact('invoices', 'summary'));
    }

    /**
     * Display user reports.
     */
    public function users()
    {
        $users = User::with(['department', 'designation'])->orderBy('user_surname')->get();

        $summary = [
            'total_users' => $users->count(),
            'active_users' => $users->where('user_active', true)->count(),
            'online_users' => $users->where('user_online', true)->count(),
            'users_this_month' => $users->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('reports.users', compact('users', 'summary'));
    }

    /**
     * Display audit trail reports.
     */
    public function auditTrail(Request $request)
    {
        $query = AuditTrail::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('reports.audit-trail', compact('activities'));
    }

    /**
     * Export reports.
     */
    public function export(Request $request)
    {
        $type = $request->input('type');
        $format = $request->input('format', 'csv');

        switch ($type) {
            case 'invoices':
                return $this->exportInvoices($request, $format);
            case 'users':
                return $this->exportUsers($format);
            case 'audit_trail':
                return $this->exportAuditTrail($request, $format);
            default:
                return back()->with('error', 'Invalid export type');
        }
    }

    private function exportInvoices($request, $format)
    {
        $query = Invoice::with(['creator', 'items']);

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->get();

        $filename = 'invoices_' . date('Y-m-d_H-i-s') . '.' . $format;

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($invoices) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, ['Invoice No', 'Buyer Name', 'Amount', 'Tax', 'Total', 'Type', 'Status', 'Date', 'Created By']);
                
                foreach ($invoices as $invoice) {
                    fputcsv($file, [
                        $invoice->invoice_no,
                        $invoice->buyer_name,
                        $invoice->invoice_amount,
                        $invoice->tax_amount,
                        $invoice->total_amount,
                        $invoice->invoice_type,
                        $invoice->status,
                        $invoice->invoice_date->format('Y-m-d'),
                        $invoice->creator->full_name,
                    ]);
                }
                
                fclose($file);
            }, $filename);
        }

        return back()->with('error', 'Export format not supported');
    }

    private function exportUsers($format)
    {
        $users = User::with(['department', 'designation'])->orderBy('user_surname')->get();

        $filename = 'users_' . date('Y-m-d_H-i-s') . '.' . $format;

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($users) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, ['Name', 'Username', 'Email', 'Department', 'Designation', 'Status', 'Last Login']);
                
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->full_name,
                        $user->user_name,
                        $user->email,
                        $user->department->dept_name ?? '',
                        $user->designation->designation_name ?? '',
                        $user->user_active ? 'Active' : 'Inactive',
                        $user->user_last_logged_in ? $user->user_last_logged_in->format('Y-m-d H:i:s') : 'Never',
                    ]);
                }
                
                fclose($file);
            }, $filename);
        }

        return back()->with('error', 'Export format not supported');
    }

    private function exportAuditTrail($request, $format)
    {
        $query = AuditTrail::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('created_at', 'desc')->get();

        $filename = 'audit_trail_' . date('Y-m-d_H-i-s') . '.' . $format;

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($activities) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, ['Date', 'User', 'Action', 'Description', 'IP Address']);
                
                foreach ($activities as $activity) {
                    fputcsv($file, [
                        $activity->created_at->format('Y-m-d H:i:s'),
                        $activity->user->full_name ?? 'System',
                        $activity->action,
                        $activity->description,
                        $activity->ip_address,
                    ]);
                }
                
                fclose($file);
            }, $filename);
        }

        return back()->with('error', 'Export format not supported');
    }
} 