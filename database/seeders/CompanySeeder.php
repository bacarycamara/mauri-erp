<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'MauriERP Demo Company',
                'email' => 'contact@maurierp.test',
                'phone' => '22200000000',
                'nif' => 'NIF-0001',
                'rc' => 'RC-0001',
                'address' => 'Nouakchott',
                'city' => 'Nouakchott',
                'country' => 'Mauritanie',
                'currency' => 'MRU',
                'default_vat' => 18,
                'invoice_prefix' => 'FAC',
                'invoice_format' => '{prefix}-{number}',
                'invoice_counter' => 1,
                'invoice_footer' => 'Merci pour votre confiance.',
                'website' => 'www.maurierp.test',
                'bank_account' => 'Bank MRU 000001',
                'is_active' => true,
            ]
        );
    }
}