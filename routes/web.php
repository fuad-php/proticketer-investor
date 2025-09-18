<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // User Management Routes
    Route::middleware(['role:super_admin'])->group(function () {
        Route::resource('users', UserController::class);
    });
    
    // Investor Management Routes
    Route::middleware(['permission:view investors|create investors|edit investors|delete investors'])->group(function () {
        Route::resource('investors', InvestorController::class);
    });
    
    // Order Management Routes
    Route::middleware(['permission:view orders|create orders|edit orders|delete orders'])->group(function () {
        Route::resource('orders', OrderController::class);
    });
    
    // Investment Management Routes
    Route::middleware(['permission:view investments|create investments|edit investments|delete investments'])->group(function () {
        Route::resource('investments', InvestmentController::class);
    });
    
    // Approval Management Routes
    Route::middleware(['permission:view approvals|approve orders|reject orders'])->group(function () {
        Route::resource('approvals', ApprovalController::class);
        Route::post('approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('approvals/{approval}/request-changes', [ApprovalController::class, 'requestChanges'])->name('approvals.request-changes');
        Route::get('orders/{order}/workflow-status', [ApprovalController::class, 'getWorkflowStatus'])->name('orders.workflow-status');
    });
    
    // Client Management Routes
    Route::middleware(['permission:view clients|create clients|edit clients|delete clients'])->group(function () {
        Route::resource('clients', ClientController::class);
    });
    
    // Inquiry Management Routes
    Route::middleware(['permission:view inquiries|create inquiries|edit inquiries|respond inquiries'])->group(function () {
        Route::resource('inquiries', InquiryController::class);
        Route::post('inquiries/{inquiry}/respond', [InquiryController::class, 'respond'])->name('inquiries.respond');
        Route::post('inquiries/{inquiry}/assign', [InquiryController::class, 'assign'])->name('inquiries.assign');
        Route::post('inquiries/{inquiry}/update-status', [InquiryController::class, 'updateStatus'])->name('inquiries.update-status');
        Route::post('inquiries/{inquiry}/add-notes', [InquiryController::class, 'addNotes'])->name('inquiries.add-notes');
        Route::get('inquiries/export', [InquiryController::class, 'export'])->name('inquiries.export');
    });
    
    // Transaction Management Routes
    Route::middleware(['permission:view transactions|create transactions|edit transactions|delete transactions'])->group(function () {
        Route::resource('transactions', TransactionController::class);
    });
    
    // Statement Management Routes
    Route::middleware(['permission:view statements|create statements|approve statements'])->group(function () {
        Route::resource('statements', StatementController::class);
        Route::post('statements/{statement}/approve', [StatementController::class, 'approve'])->name('statements.approve');
        Route::get('statements/{statement}/download', [StatementController::class, 'download'])->name('statements.download');
        Route::post('investments/{investment}/generate-statement', [StatementController::class, 'generateForInvestment'])->name('investments.generate-statement');
        Route::post('transactions/{transaction}/generate-receipt', [StatementController::class, 'generateReceiptForTransaction'])->name('transactions.generate-receipt');
    });
    
    // Receipt Management Routes
    Route::middleware(['permission:view receipts|create receipts|verify receipts'])->group(function () {
        Route::resource('receipts', ReceiptController::class);
        Route::post('receipts/{receipt}/verify', [ReceiptController::class, 'verify'])->name('receipts.verify');
        Route::get('receipts/{receipt}/download', [ReceiptController::class, 'download'])->name('receipts.download');
        Route::post('receipts/bulk-generate', [ReceiptController::class, 'bulkGenerate'])->name('receipts.bulk-generate');
    });
    
    // Accounting Management Routes
    Route::middleware(['permission:view accounting|create accounting|approve accounting|reverse accounting'])->group(function () {
        Route::resource('accounting', AccountingController::class);
        Route::post('accounting/{transaction}/approve', [AccountingController::class, 'approve'])->name('accounting.approve');
        Route::post('accounting/{transaction}/reject', [AccountingController::class, 'reject'])->name('accounting.reject');
        Route::post('accounting/{transaction}/reverse', [AccountingController::class, 'reverse'])->name('accounting.reverse');
        Route::get('accounting/reports', [AccountingController::class, 'reports'])->name('accounting.reports');
    });
    
    // Investor-specific routes
    Route::middleware(['role:investor'])->group(function () {
        Route::get('investor/dashboard', [InvestorController::class, 'dashboard'])->name('investor.dashboard');
        Route::get('investor/statements', [InvestorController::class, 'statements'])->name('investor.statements');
        Route::get('investor/statements/download/{id}', [InvestorController::class, 'downloadStatement'])->name('investor.statements.download');
        Route::get('investor/receipts', [InvestorController::class, 'receipts'])->name('investor.receipts');
        Route::get('investor/receipts/download/{id}', [InvestorController::class, 'downloadReceipt'])->name('investor.receipts.download');
        Route::get('investor/export-data', [InvestorController::class, 'exportData'])->name('investor.export-data');
    });
    
    // Client-specific routes
    Route::middleware(['role:client'])->group(function () {
        Route::get('client/inquiries', [InquiryController::class, 'clientInquiries'])->name('client.inquiries');
        Route::post('client/inquiries', [InquiryController::class, 'store'])->name('client.inquiries.store');
    });
    
    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('recent', [NotificationController::class, 'recent'])->name('recent');
        Route::delete('{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });
    
    // Report routes
    Route::middleware(['permission:view reports|export reports'])->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/investor-statements', [ReportController::class, 'investorStatements'])->name('reports.investor-statements');
        Route::get('reports/profit-summary', [ReportController::class, 'profitSummary'])->name('reports.profit-summary');
        Route::get('reports/cashflow', [ReportController::class, 'cashflow'])->name('reports.cashflow');
        Route::get('reports/outstanding-payments', [ReportController::class, 'outstandingPayments'])->name('reports.outstanding-payments');
        Route::get('reports/approval-status', [ReportController::class, 'approvalStatus'])->name('reports.approval-status');
        Route::get('reports/client-inquiries', [ReportController::class, 'clientInquiries'])->name('reports.client-inquiries');
        Route::get('reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
        Route::get('reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    });
    
    // Security routes
    Route::middleware(['permission:view audit logs|manage security'])->group(function () {
        Route::get('security', [SecurityController::class, 'dashboard'])->name('security.dashboard');
        Route::get('security/audit-logs', [SecurityController::class, 'auditLogs'])->name('security.audit-logs');
        Route::get('security/user-security', [SecurityController::class, 'userSecurity'])->name('security.user-security');
        Route::put('security/user-security/{user}', [SecurityController::class, 'updateUserSecurity'])->name('security.update-user-security');
        Route::get('security/failed-logins', [SecurityController::class, 'failedLogins'])->name('security.failed-logins');
        Route::post('security/clear-failed-logins', [SecurityController::class, 'clearFailedLogins'])->name('security.clear-failed-logins');
        Route::get('security/export-audit-logs', [SecurityController::class, 'exportAuditLogs'])->name('security.export-audit-logs');
    });
    
    // Two-Factor Authentication routes
    Route::prefix('2fa')->name('2fa.')->group(function () {
        Route::get('setup', [TwoFactorController::class, 'showSetupForm'])->name('setup');
        Route::post('enable', [TwoFactorController::class, 'enable'])->name('enable');
        Route::get('disable', [TwoFactorController::class, 'showDisableForm'])->name('disable');
        Route::post('disable', [TwoFactorController::class, 'disable'])->name('disable');
        Route::get('verify', [TwoFactorController::class, 'showVerifyForm'])->name('verify');
        Route::post('verify', [TwoFactorController::class, 'verify'])->name('verify');
        Route::post('verify-backup', [TwoFactorController::class, 'verifyWithBackupCode'])->name('verify-backup');
        Route::post('regenerate-backup-codes', [TwoFactorController::class, 'regenerateBackupCodes'])->name('regenerate-backup-codes');
    });
    
    // Document Management routes
    Route::middleware(['permission:view documents|create documents|edit documents|delete documents|approve documents'])->group(function () {
        Route::resource('documents', DocumentController::class);
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::get('documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
        Route::post('documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
        Route::post('documents/{document}/archive', [DocumentController::class, 'archive'])->name('documents.archive');
        Route::post('documents/{document}/restore', [DocumentController::class, 'restore'])->name('documents.restore');
        Route::post('documents/bulk-action', [DocumentController::class, 'bulkAction'])->name('documents.bulk-action');
        Route::get('documents/stats', [DocumentController::class, 'stats'])->name('documents.stats');
    });
});

require __DIR__.'/auth.php';
