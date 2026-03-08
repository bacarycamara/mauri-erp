<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | EDIT (Mono-Entreprise sécurisé)
    |--------------------------------------------------------------------------
    */
    public function edit()
    {
        $company = $this->getCompany();

        return view('admin.company.edit', compact('company'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $company = $this->getCompany();

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'nif'             => 'nullable|string|max:100',
            'rc'              => 'nullable|string|max:100',
            'address'         => 'nullable|string|max:255',
            'city'            => 'nullable|string|max:100',
            'country'         => 'nullable|string|max:100',
            'currency'        => 'required|string|max:10',
            'default_vat'     => 'required|numeric|min:0|max:100',
            'invoice_prefix'  => 'required|string|max:20',
            'invoice_format'  => 'required|string|max:255',
            'invoice_footer'  => 'nullable|string',
            'website'         => 'nullable|url|max:255',
            'bank_account'    => 'nullable|string|max:255',
            'logo'            => 'nullable|image|max:2048',
        ]);

        /*
        |--------------------------------------------------------------------------
        | LOGO UPLOAD (SAFE ERP)
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('logo')) {

            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }

            $filename = 'company_' . Str::uuid() . '.' .
                        $request->file('logo')->extension();

            $path = $request->file('logo')
                ->storeAs('companies', $filename, 'public');

            $validated['logo'] = $path;
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE DATA
        |--------------------------------------------------------------------------
        */
        $company->update($validated);

        /*
        |--------------------------------------------------------------------------
        | CLEAR CACHE
        |--------------------------------------------------------------------------
        */
        Cache::forget('company');

        return back()->with(
            'success',
            'Informations de l’entreprise mises à jour avec succès.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESET INVOICE COUNTER
    |--------------------------------------------------------------------------
    */
    public function resetInvoiceCounter()
    {
        $company = $this->getCompany();

        $company->update([
            'invoice_counter' => 1
        ]);

        Cache::forget('company');

        return back()->with(
            'success',
            'Compteur de factures réinitialisé.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE: GET OR CREATE COMPANY (ERP Mono Mode)
    |--------------------------------------------------------------------------
    */
    private function getCompany(): Company
    {
        return Cache::rememberForever('company', function () {

            return Company::first() ?? Company::create([
                'name'            => 'MauriERP',
                'country'         => 'Mauritanie',
                'currency'        => 'MRU',
                'invoice_prefix'  => 'FAC',
                'invoice_format'  => '{prefix}-{number}',
                'invoice_counter' => 1,
                'default_vat'     => 0,
                'is_active'       => true,
            ]);
        });
    }
}