<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\GoodsController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EfrisApiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication Routes
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard API endpoints for charts
    Route::get('/dashboard/invoice-stats-by-type', [DashboardController::class, 'getInvoiceStatsByType']);
    Route::get('/dashboard/invoice-stats-by-status', [DashboardController::class, 'getInvoiceStatsByStatus']);
    Route::get('/dashboard/user-activity-stats', [DashboardController::class, 'getUserActivityStats']);
    Route::get('/dashboard/customer-stats', [DashboardController::class, 'getCustomerStats']);
    
    // EFRIS API Routes
    Route::prefix('efris')->name('efris.')->group(function () {
        Route::get('/settings', [EfrisApiController::class, 'settings'])->name('settings');
        Route::put('/settings', [EfrisApiController::class, 'updateSettings'])->name('settings.update');
        Route::get('/test-connection', [EfrisApiController::class, 'testConnection'])->name('test-connection');
        Route::get('/get-status', [EfrisApiController::class, 'getStatus'])->name('get-status');
        Route::get('/validate-config', [EfrisApiController::class, 'validateConfig'])->name('validate-config');
        Route::get('/get-logs', [EfrisApiController::class, 'getLogs'])->name('get-logs');
        Route::get('/test-page', function () {
            return view('efris.test');
        })->name('test-page');
    });
    
    // Invoice Routes
    Route::resource('invoices', InvoiceController::class)->parameters(['invoices' => 'id']);
    Route::post('/invoices/{id}/submit-efris', [InvoiceController::class, 'submitToEfris'])->name('invoices.submit-efris');
    Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/goods/search', [InvoiceController::class, 'getGoods'])->name('invoices.goods.search');
    
    // Credit Note Routes
    Route::resource('credit-notes', CreditNoteController::class)->parameters(['credit-notes' => 'id']);
    Route::post('/credit-notes/{id}/submit-efris', [CreditNoteController::class, 'submitToEfris'])->name('credit-notes.submit-efris');
    Route::post('/credit-notes/{id}/cancel', [CreditNoteController::class, 'cancel'])->name('credit-notes.cancel');
    Route::get('/credit-notes/{id}/print', [CreditNoteController::class, 'print'])->name('credit-notes.print');
    Route::get('/credit-notes/invoice/{invoiceId}/items', [CreditNoteController::class, 'getInvoiceItems'])->name('credit-notes.invoice-items');
    
    // Stock Management Routes
    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::get('/stocks/all', [StockController::class, 'allStock'])->name('stocks.all');
    Route::get('/stocks/create', [StockController::class, 'create'])->name('stocks.create');
    Route::post('/stocks', [StockController::class, 'store'])->name('stocks.store');
    Route::get('/stocks/{id}', [StockController::class, 'show'])->name('stocks.show');
    Route::get('/stocks/{id}/increase', [StockController::class, 'increaseStock'])->name('stocks.increase');
    Route::put('/stocks/{id}', [StockController::class, 'update'])->name('stocks.update');
    Route::delete('/stocks/{id}', [StockController::class, 'destroy'])->name('stocks.destroy');
    
    // Stock Decrease Routes
    Route::get('/stocks/decrease', [StockController::class, 'decreaseStock'])->name('stocks.decrease');
    Route::get('/stocks/decrease/create', [StockController::class, 'create'])->name('stocks.decrease.create');
    Route::post('/stocks/decrease', [StockController::class, 'storeDecrease'])->name('stocks.decrease.store');
    Route::get('/stocks/decrease/{id}', [StockController::class, 'showDecrease'])->name('stocks.decrease.show');
    Route::get('/stocks/decrease/{id}/edit', [StockController::class, 'decreaseStockForm'])->name('stocks.decrease.edit');
    Route::put('/stocks/decrease/{id}', [StockController::class, 'updateDecrease'])->name('stocks.decrease.update');
    
    // Stock AJAX Routes
    Route::post('/stocks/check-quantity', [StockController::class, 'checkStockQuantity'])->name('stocks.check-quantity');
    Route::get('/stocks/available/{itemCode}', [StockController::class, 'getAvailableStock'])->name('stocks.available');
    
    // Goods Routes
    Route::resource('goods', GoodsController::class)->parameters(['goods' => 'id']);
    Route::post('/goods/{id}/toggle-status', [GoodsController::class, 'toggleStatus'])->name('goods.toggle-status');
    
    // User Management Routes
    Route::resource('users', UserController::class)->parameters(['users' => 'id']);
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/{id}/roles', [UserController::class, 'getRoles'])->name('users.roles');
    Route::get('/users/{id}/permissions', [UserController::class, 'getPermissions'])->name('users.permissions');
    Route::post('/users/bulk-assign-roles', [UserController::class, 'bulkAssignRoles'])->name('users.bulk-assign-roles');
    
    // Customer Management Routes
    Route::resource('customers', CustomerController::class)->parameters(['customers' => 'id']);
    Route::post('/customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::get('/customers/{id}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    Route::get('/customers/search/ajax', [CustomerController::class, 'search'])->name('customers.search.ajax');
    
    // Change Password
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Department Routes
    Route::resource('departments', DepartmentController::class)->parameters(['departments' => 'id']);
    
    // Designation Routes
    Route::resource('designations', DesignationController::class)->parameters(['designations' => 'id']);
    
    // Role Management Routes
    Route::resource('roles', RoleController::class)->parameters(['roles' => 'id']);
    Route::post('/roles/{id}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');
    Route::get('/roles/{id}/permissions', [RoleController::class, 'getPermissions'])->name('roles.permissions');
    Route::get('/roles/{id}/users', [RoleController::class, 'getUsers'])->name('roles.users');
    Route::post('/roles/{id}/duplicate', [RoleController::class, 'duplicate'])->name('roles.duplicate');
    
    // Permission Management Routes
    Route::resource('permissions', PermissionController::class)->parameters(['permissions' => 'id']);
    Route::post('/permissions/{id}/toggle-status', [PermissionController::class, 'toggleStatus'])->name('permissions.toggle-status');
    Route::get('/permissions/grouped', [PermissionController::class, 'getGroupedPermissions'])->name('permissions.grouped');
    Route::get('/permissions/{id}/roles', [PermissionController::class, 'getRoles'])->name('permissions.roles');
    Route::post('/permissions/bulk-update', [PermissionController::class, 'bulkUpdate'])->name('permissions.bulk-update');
    
    // Report Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/invoices', [ReportController::class, 'invoices'])->name('reports.invoices');
    Route::get('/reports/users', [ReportController::class, 'users'])->name('reports.users');
    Route::get('/reports/audit-trail', [ReportController::class, 'auditTrail'])->name('reports.audit-trail');
    Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    
    // Test Routes (for debugging)
    Route::get('/test/efris-connection', function () {
        $efrisService = app(\App\Services\EfrisService::class);
        $result = $efrisService->testConnection();
        return response()->json($result);
    })->name('test.efris-connection');
    
    // Debug EFRIS status route
    Route::get('/test/efris-status', function () {
        try {
            $efrisService = app(\App\Services\EfrisService::class);
            $config = $efrisService->getEfrisConfig();
            $connectionTest = $efrisService->testConnection();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'config' => $config,
                    'connection' => $connectionTest,
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get EFRIS status: ' . $e->getMessage()
            ], 500);
        }
    })->name('test.efris-status');
    
    // Simple debug route
    Route::get('/debug/efris', function () {
        return response()->json([
            'success' => true,
            'message' => 'Debug route working',
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    })->name('debug.efris');
    
    // QR code and barcode routes for stocks
    Route::get('/stocks/{id}/qrcode', [StockController::class, 'showQrCode'])->name('stocks.qrcode');
    Route::get('/stocks/{id}/barcode', [StockController::class, 'showBarcode'])->name('stocks.barcode');
    // QR code and barcode routes for goods
    Route::get('/goods/{id}/qrcode', [GoodsController::class, 'showQrCode'])->name('goods.qrcode');
    Route::get('/goods/{id}/barcode', [GoodsController::class, 'showBarcode'])->name('goods.barcode');
    
});

// Settings routes
Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-efris', [SettingsController::class, 'testEfrisConnection'])->name('settings.test-efris');
    Route::get('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    Route::get('/settings/export', [SettingsController::class, 'export'])->name('settings.export');
    Route::post('/settings/import', [SettingsController::class, 'import'])->name('settings.import');
});

// Fallback route
Route::fallback(function () {
    return redirect('/dashboard');
});
