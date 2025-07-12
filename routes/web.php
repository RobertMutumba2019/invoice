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
    Route::get('/users', [AuthController::class, 'users'])->name('users');
    Route::get('/users/add', [AuthController::class, 'showAddUser'])->name('users.add');
    Route::post('/users', [AuthController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}/edit', [AuthController::class, 'showEditUser'])->name('users.edit');
    Route::put('/users/{id}', [AuthController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AuthController::class, 'deleteUser'])->name('users.delete');
    
    // Change Password
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Department Routes
    Route::resource('departments', DepartmentController::class)->parameters(['departments' => 'id']);
    
    // Designation Routes
    Route::resource('designations', DesignationController::class)->parameters(['designations' => 'id']);
    
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
