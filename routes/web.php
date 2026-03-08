<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\{
    DashboardController,
    CompanyController,
    CategoryController,
    ProductController,
    CustomerController,
    SupplierController,
    PurchaseController,
    SaleController,
    PaymentController,
    CashRegisterController,
    CashTransactionController,
    ExpenseController,
    ReportController,
    UserController,
    RoleController,
    BackupController,
    AuditLogController,
    StockMovementController
};

/*
|--------------------------------------------------------------------------
| ROOT REDIRECTION
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED AREA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/profile', 'profile')->name('profile');
});


/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'auto.permission'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    /*
    |----------------------------------------------------------------------
    | COMPANY
    |----------------------------------------------------------------------
    */
    Route::prefix('company')->name('company.')->group(function () {
        Route::get('/',               [CompanyController::class, 'edit'])->name('edit');
        Route::put('/',               [CompanyController::class, 'update'])->name('update');
        Route::post('/reset-invoice', [CompanyController::class, 'resetInvoiceCounter'])->name('reset-invoice');
    });

    /*
    |----------------------------------------------------------------------
    | USERS & ROLES
    |----------------------------------------------------------------------
    */
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    /*
    |----------------------------------------------------------------------
    | PRODUCTS & CATEGORIES
    |----------------------------------------------------------------------
    */
    Route::resource('categories', CategoryController::class);
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::resource('products', ProductController::class);

    /*
    |----------------------------------------------------------------------
    | STOCK MOVEMENTS
    |----------------------------------------------------------------------
    */
    Route::prefix('stock-movements')->name('stock-movements.')->group(function () {
        Route::get('/',                   [StockMovementController::class, 'index'])->name('index');
        Route::post('/',                  [StockMovementController::class, 'store'])->name('store');
        Route::get('/product/{product}',  [StockMovementController::class, 'product'])->name('product');
        Route::delete('/{stockMovement}', [StockMovementController::class, 'destroy'])->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | CUSTOMERS & SUPPLIERS
    |----------------------------------------------------------------------
    */
    Route::resource('customers', CustomerController::class);
    Route::get('suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
    Route::resource('suppliers', SupplierController::class);

    /*
    |----------------------------------------------------------------------
    | PURCHASES
    |----------------------------------------------------------------------
    */
    Route::resource('purchases', PurchaseController::class);
    Route::prefix('purchases/{purchase}')->name('purchases.')->group(function () {
        Route::post('confirm', [PurchaseController::class, 'confirm'])->name('confirm');
        Route::post('cancel',  [PurchaseController::class, 'cancel'])->name('cancel');
        Route::post('pay',     [PurchaseController::class, 'registerPayment'])->name('pay');
        Route::get('pdf',      [PurchaseController::class, 'downloadPdf'])->name('pdf');
    });
    Route::get('purchases-export', [PurchaseController::class, 'export'])->name('purchases.export');

    /*
    |----------------------------------------------------------------------
    | SALES
    |----------------------------------------------------------------------
    */
    Route::get('sales/export', [SaleController::class, 'export'])->name('sales.export');
    Route::resource('sales', SaleController::class);
    Route::prefix('sales/{sale}')->group(function () {
        Route::post('confirm', [SaleController::class, 'confirm'])->name('sales.confirm');
        Route::post('cancel',  [SaleController::class, 'cancel'])->name('sales.cancel');
        Route::post('pay',     [SaleController::class, 'registerPayment'])->name('sales.pay');
        Route::get('pdf',      [SaleController::class, 'pdf'])->name('sales.pdf');
    });

    /*
    |----------------------------------------------------------------------
    | PAYMENTS
    |----------------------------------------------------------------------
    */
    Route::resource('payments', PaymentController::class);
    Route::post('payments/ajax-store', [PaymentController::class, 'ajaxStore'])->name('payments.ajax-store');
    Route::prefix('payments/{payment}')->name('payments.')->group(function () {
        Route::post('cancel',  [PaymentController::class, 'cancel'])->name('cancel');
        Route::get('pdf',      [PaymentController::class, 'pdf'])->name('pdf');
        Route::get('print',    [PaymentController::class, 'print'])->name('print');
        Route::get('printer',  [PaymentController::class, 'printer'])->name('printer');
    });
    Route::get('payments-export', [PaymentController::class, 'export'])->name('payments.export');

    /*
    |----------------------------------------------------------------------
    | CASH TRANSACTIONS
    |----------------------------------------------------------------------
    */
    Route::prefix('cash-transactions')->name('cash-transactions.')->group(function () {
        Route::get('/',                      [CashTransactionController::class, 'index'])->name('index');
        Route::post('/',                     [CashTransactionController::class, 'store'])->name('store');
        Route::delete('/{cashTransaction}',  [CashTransactionController::class, 'destroy'])->name('destroy');
        Route::get('/{cashRegister}/pdf',    [CashTransactionController::class, 'pdf'])->name('pdf');
        Route::get('/{cashRegister}/print',  [CashTransactionController::class, 'print'])->name('print');
    });

    /*
    |----------------------------------------------------------------------
    | CASH REGISTERS
    |----------------------------------------------------------------------
    */
    Route::prefix('cash-registers')->name('cash-registers.')->group(function () {
        Route::get('/',                       [CashRegisterController::class, 'index'])->name('index');
        Route::get('/create',                 [CashRegisterController::class, 'create'])->name('create');
        Route::post('/',                      [CashRegisterController::class, 'store'])->name('store');
        Route::post('/open',                  [CashRegisterController::class, 'open'])->name('open');
        Route::get('/{cash_register}',        [CashRegisterController::class, 'show'])->name('show');
        Route::delete('/{cash_register}',     [CashRegisterController::class, 'destroy'])->name('destroy');
        Route::post('/{cash_register}/close', [CashRegisterController::class, 'close'])->name('close');
        Route::get('/{cash_register}/pdf',    [CashRegisterController::class, 'report'])->name('pdf');
    });

    /*
    |----------------------------------------------------------------------
    | EXPENSES
    |----------------------------------------------------------------------
    */
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/',               [ExpenseController::class, 'index'])->name('index');
        Route::get('/create',         [ExpenseController::class, 'create'])->name('create');
        Route::get('/export',         [ExpenseController::class, 'export'])->name('export');
        Route::post('/',              [ExpenseController::class, 'store'])->name('store');
        Route::get('/{expense}',      [ExpenseController::class, 'show'])->name('show');
        Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->name('edit');
        Route::put('/{expense}',      [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}',   [ExpenseController::class, 'destroy'])->name('destroy');
        Route::post('/{expense}/approve', [ExpenseController::class, 'approve'])->name('approve');
        Route::post('/{expense}/cancel',  [ExpenseController::class, 'cancel'])->name('cancel');
        Route::get('/{expense}/pdf',  [ExpenseController::class, 'downloadPdf'])->name('pdf');
    });

    /*
    |----------------------------------------------------------------------
    | REPORTS
    |----------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',               [ReportController::class, 'index'])->name('index');
        Route::get('/dashboard',      [ReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/financial',      [ReportController::class, 'financial'])->name('financial');
        Route::get('/sales',          [ReportController::class, 'sales'])->name('sales');
        Route::get('/purchases',      [ReportController::class, 'purchases'])->name('purchases');
        Route::get('/expenses',       [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/stock',          [ReportController::class, 'stock'])->name('stock');
        Route::get('/cash-registers', [ReportController::class, 'cashRegisters'])->name('cash-registers');
        Route::get('/top-products',   [ReportController::class, 'topProducts'])->name('top-products');
        Route::get('/export',         [ReportController::class, 'export'])->name('export');
    });

    /*
    |----------------------------------------------------------------------
    | BACKUPS
    |----------------------------------------------------------------------
    */
    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/',                  [BackupController::class, 'index'])->name('index');
        Route::post('/create',           [BackupController::class, 'create'])->name('create');
        Route::get('/download/{file}',   [BackupController::class, 'download'])->name('download');
        Route::delete('/destroy/{file}', [BackupController::class, 'destroy'])->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | AUDIT LOGS                               export ajouté
    |----------------------------------------------------------------------
    */
    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/',        [AuditLogController::class, 'index'])->name('index');
        Route::get('/export',  [AuditLogController::class, 'export'])->name('export');  
        Route::delete('clear', [AuditLogController::class, 'clear'])->name('clear');
    });

}); // END ADMIN

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout');

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| FALLBACK
|--------------------------------------------------------------------------
*/
Route::fallback(fn () => abort(404));