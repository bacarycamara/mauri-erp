<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AutoPermissionMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | MODULES NON PLURALISÉS (exceptions manuelles)
    |--------------------------------------------------------------------------
    */
    protected array $singular = [
        'company',
        'stock',
        'dashboard',
    ];

    /*
    |--------------------------------------------------------------------------
    | MAPPING CONTROLLER → MODULE
    |--------------------------------------------------------------------------
    */
    protected array $moduleMap = [
        'CompanyController'         => 'company',
        'StockMovementController'   => 'stock_movements',
        'CashRegisterController'    => 'cash_registers',
        'CashTransactionController' => 'cash_transactions',
        'AuditLogController'        => 'audit_logs',
        'BackupController'          => 'settings',
        'ReportController'          => 'reports',
        'CategoryController'        => 'categories',
        'ProductController'         => 'products',
        'CustomerController'        => 'customers',
        'SupplierController'        => 'suppliers',
        'PurchaseController'        => 'purchases',
        'SaleController'            => 'sales',
        'PaymentController'         => 'payments',
        'ExpenseController'         => 'expenses',
        'UserController'            => 'users',
        'RoleController'            => 'roles',
    ];

    /*
    |--------------------------------------------------------------------------
    | MAPPING METHOD → ACTION (lié au RoleSeeder)
    |--------------------------------------------------------------------------
    */
    protected array $actionMap = [

        'index'        => 'view',
        'show'         => 'view',

        'create'       => 'create',
        'store'        => 'create',
        'ajaxStore'    => 'create',
        'registerPayment' => 'create',

        'edit'         => 'edit',
        'update'       => 'edit',
        'toggleStatus' => 'edit',
        'resetInvoiceCounter' => 'edit',
        'open'         => 'edit',
        'close'        => 'edit',

        'destroy'      => 'delete',
        'restore'      => 'delete',

        //  CORRECTION PRINCIPALE
        'confirm'      => 'confirm',

        'cancel'       => 'cancel',
        'approve'      => 'approve',

        'export'       => 'export',

        'downloadPdf'  => 'print',
        'pdf'          => 'print',
        'print'        => 'print',
        'printer'      => 'print',

        'report'       => 'view',
        'financial'    => 'view',
        'sales'        => 'view',
        'purchases'    => 'view',
        'expenses'     => 'view',
        'stock'        => 'view',
        'cashRegisters'=> 'view',
        'topProducts'  => 'view',
        'dashboard'    => 'view',
        'product'      => 'view',
    ];

    /*
    |--------------------------------------------------------------------------
    | HANDLE REQUEST
    |--------------------------------------------------------------------------
    */
    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Vérification connexion
        |--------------------------------------------------------------------------
        */
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | ADMIN / SUPER ADMIN → accès total
        |--------------------------------------------------------------------------
        */
        $roleName = strtolower($user->roles->first()?->name ?? '');

        if (in_array($roleName, ['admin', 'super admin'])) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Détection de l'action Laravel
        |--------------------------------------------------------------------------
        */
        $action = Route::currentRouteAction();

        if (!$action || !str_contains($action, '@')) {
            return $next($request);
        }

        [$controllerClass, $method] = explode('@', $action);
        $controllerBasename = class_basename($controllerClass);

        /*
        |--------------------------------------------------------------------------
        | Si méthode non mappée → on laisse passer
        |--------------------------------------------------------------------------
        */
        if (!isset($this->actionMap[$method])) {
            return $next($request);
        }

        $actionVerb = $this->actionMap[$method];

        /*
        |--------------------------------------------------------------------------
        | Détection du module
        |--------------------------------------------------------------------------
        */
        if (isset($this->moduleMap[$controllerBasename])) {

            $module = $this->moduleMap[$controllerBasename];

        } else {

            $module = str_replace('Controller', '', $controllerBasename);
            $module = Str::snake($module);

            if (!in_array($module, $this->singular)) {
                $module = Str::plural($module);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Génération permission finale
        |--------------------------------------------------------------------------
        */
        $permission = "{$actionVerb} {$module}";

        /*
        |--------------------------------------------------------------------------
        | Vérification permission
        |--------------------------------------------------------------------------
        */
        if (!$user->can($permission)) {

            abort(403, 'Accès non autorisé.');

        }

        return $next($request);
    }
}