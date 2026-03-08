<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Clean Tables (NO TRUNCATE FK TABLES)
        |--------------------------------------------------------------------------
        */
        DB::table('model_has_roles')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('roles')->delete();
        DB::table('users')->delete();
        DB::table('categories')->delete();

        /*
        |--------------------------------------------------------------------------
        | Call Seeders
        |--------------------------------------------------------------------------
        */
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Super Admin
        |--------------------------------------------------------------------------
        */
        $admin = User::updateOrCreate(
            ['email' => 'admin@maurierp.mr'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password123'),
                'phone'    => '22200000000',
                'status'   => 'active',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Assign Admin Role
        |--------------------------------------------------------------------------
        */
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }
    }
}