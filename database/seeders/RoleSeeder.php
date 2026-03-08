<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ─────────────────────────────────────────────────────────────
        // TOUTES LES PERMISSIONS — calées sur web.php et la sidebar
        // ─────────────────────────────────────────────────────────────
        $permissions = [
            // Dashboard
            'view dashboard',
            'view dashboard.financials',   // ← clé principale : montants globaux

            // Catalogue
            'view products',    'create products',    'edit products',    'delete products',    'export products',
            'view categories',  'create categories',  'edit categories',  'delete categories',

            // Stock
            'view stock_movements',  'create stock_movements',  'delete stock_movements',

            // Fournisseurs
            'view suppliers',   'create suppliers',   'edit suppliers',   'delete suppliers',   'export suppliers',

            // Clients
            'view customers',   'create customers',   'edit customers',   'delete customers',

            // Achats
            'view purchases',   'create purchases',   'edit purchases',   'delete purchases',
            'confirm purchases','cancel purchases',   'pay purchases',
            'print purchases',  'export purchases',

            // Ventes
            'view sales',       'create sales',       'edit sales',       'delete sales',
            'confirm sales',    'cancel sales',       'pay sales',
            'print sales',      'export sales',

            // Paiements
            'view payments',    'create payments',    'edit payments',    'delete payments',
            'cancel payments',  'print payments',     'export payments',

            // Caisses
            'view cash_registers',   'create cash_registers',   'delete cash_registers',
            'open cash_registers',   'close cash_registers',    'print cash_registers',

            // Transactions
            'view cash_transactions',  'create cash_transactions',  'delete cash_transactions',
            'print cash_transactions',

            // Dépenses
            'view expenses',    'create expenses',    'edit expenses',    'delete expenses',
            'approve expenses', 'cancel expenses',    'export expenses',  'print expenses',

            // Rapports
            'view reports',     'export reports',

            // Utilisateurs & Rôles
            'view users',       'create users',       'edit users',       'delete users',
            'view roles',       'create roles',       'edit roles',       'delete roles',

            // Entreprise
            'view company',     'edit company',

            // Sauvegardes
            'view settings',    'create settings',    'delete settings',

            // Logs
            'view audit_logs',  'export audit_logs',  'delete audit_logs',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ─────────────────────────────────────────────────────────────
        // RÔLES
        // ─────────────────────────────────────────────────────────────
        $admin        = Role::firstOrCreate(['name' => 'Admin',        'guard_name' => 'web']);
        $gestionnaire = Role::firstOrCreate(['name' => 'Gestionnaire', 'guard_name' => 'web']);
        $comptable    = Role::firstOrCreate(['name' => 'Comptable',    'guard_name' => 'web']);
        $caissier     = Role::firstOrCreate(['name' => 'Caissier',     'guard_name' => 'web']);
        $magasinier   = Role::firstOrCreate(['name' => 'Magasinier',   'guard_name' => 'web']);
        $commercial   = Role::firstOrCreate(['name' => 'Commercial',   'guard_name' => 'web']);
        $livreur      = Role::firstOrCreate(['name' => 'Livreur',      'guard_name' => 'web']);

        // ─────────────────────────────────────────────────────────────
        // ADMIN — accès total
        // ─────────────────────────────────────────────────────────────
        $admin->syncPermissions(Permission::all());

        // ─────────────────────────────────────────────────────────────
        // GESTIONNAIRE
        // Sidebar : Dashboard, Catalogue, Stock, Achats, Ventes,
        //           Finance, Rapports
        // Dashboard : cartes financières globales ✓
        // ─────────────────────────────────────────────────────────────
        $gestionnaire->syncPermissions([
            'view dashboard',
            'view dashboard.financials',    // ← cartes montants

            'view products',    'create products',    'edit products',    'delete products',    'export products',
            'view categories',  'create categories',  'edit categories',  'delete categories',

            'view stock_movements',  'create stock_movements',

            'view suppliers',   'create suppliers',   'edit suppliers',   'export suppliers',
            'view customers',   'create customers',   'edit customers',

            'view purchases',   'create purchases',   'edit purchases',
            'confirm purchases','cancel purchases',   'pay purchases',
            'print purchases',  'export purchases',

            'view sales',       'create sales',       'edit sales',
            'confirm sales',    'cancel sales',       'pay sales',
            'print sales',      'export sales',

            'view payments',    'create payments',    'print payments',   'export payments',

            'view cash_registers',   'create cash_registers',
            'open cash_registers',   'close cash_registers',  'print cash_registers',

            'view cash_transactions',  'create cash_transactions',  'print cash_transactions',

            'view expenses',    'create expenses',    'edit expenses',
            'approve expenses', 'cancel expenses',    'export expenses',  'print expenses',

            'view reports',     'export reports',
        ]);

        // ─────────────────────────────────────────────────────────────
        // COMPTABLE
        // Sidebar : Dashboard, Achats (lecture), Ventes (lecture),
        //           Paiements, Finance complète, Rapports, Logs
        // Dashboard : cartes financières globales ✓
        // ─────────────────────────────────────────────────────────────
        $comptable->syncPermissions([
            'view dashboard',
            'view dashboard.financials',    // ← cartes montants

            'view products',
            'view categories',
            'view stock_movements',

            'view suppliers',
            'view customers',

            'view purchases',   'create purchases',   'confirm purchases',
            'pay purchases',    'print purchases',    'export purchases',

            'view sales',       'print sales',        'export sales',

            'view payments',    'create payments',    'edit payments',
            'print payments',   'export payments',

            'view cash_registers',   'print cash_registers',

            'view cash_transactions',  'create cash_transactions',  'print cash_transactions',

            'view expenses',    'create expenses',    'edit expenses',
            'approve expenses', 'print expenses',     'export expenses',

            'view reports',     'export reports',

            'view audit_logs',  'export audit_logs',
        ]);

        // ─────────────────────────────────────────────────────────────
        // CAISSIER
        // Sidebar : Dashboard, Ventes, Paiements, Finance (Caisses+Tx)
        // Dashboard : PAS de cartes montants globaux
        //             → ventes du jour/semaine + caisse ouverte
        // ─────────────────────────────────────────────────────────────
        $caissier->syncPermissions([
            'view dashboard',
            // PAS 'view dashboard.financials'

            'view products',
            'view categories',

            'view customers',   'create customers',

            'view sales',       'create sales',       'confirm sales',
            'pay sales',        'print sales',

            'view payments',    'create payments',    'print payments',

            'view cash_registers',
            'open cash_registers',   'close cash_registers',  'print cash_registers',

            'view cash_transactions',  'create cash_transactions',  'print cash_transactions',

            'view expenses',    'create expenses',    'print expenses',
        ]);

        // ─────────────────────────────────────────────────────────────
        // MAGASINIER
        // Sidebar : Dashboard, Catalogue, Stock, Achats
        // Dashboard : PAS de cartes montants → stock + achats impayés
        // ─────────────────────────────────────────────────────────────
        $magasinier->syncPermissions([
            'view dashboard',
            // PAS 'view dashboard.financials'

            'view products',    'edit products',
            'view categories',

            'view stock_movements',  'create stock_movements',

            'view suppliers',

            'view purchases',   'create purchases',   'edit purchases',
            'confirm purchases','print purchases',

            'view sales',
        ]);

        // ─────────────────────────────────────────────────────────────
        // COMMERCIAL
        // Sidebar : Dashboard, Ventes, Clients
        // Dashboard : PAS de cartes montants
        //             → ventes du jour + factures impayées + clients débiteurs
        // ─────────────────────────────────────────────────────────────
        $commercial->syncPermissions([
            'view dashboard',
            // PAS 'view dashboard.financials'

            'view products',
            'view categories',

            'view customers',   'create customers',   'edit customers',

            'view sales',       'create sales',       'edit sales',
            'confirm sales',    'cancel sales',
            'print sales',      'export sales',

            'view payments',    'print payments',
        ]);

        // ─────────────────────────────────────────────────────────────
        // LIVREUR
        // Sidebar : Dashboard uniquement
        // Dashboard : accès minimal, pas de montants
        // ─────────────────────────────────────────────────────────────
        $livreur->syncPermissions([
            'view dashboard',
            // PAS 'view dashboard.financials'

            'view sales',

            'view customers',
        ]);
    }
}