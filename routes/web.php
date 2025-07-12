<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\GoodsController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\ReportController;

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

// Fallback route
Route::fallback(function () {
    return redirect('/dashboard');
});
