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
        |----------------------------------------------------------------------
        | LOGO UPLOAD
        |----------------------------------------------------------------------
        */
        if ($request->hasFile('logo')) {

            // Supprimer l'ancien logo s'il existe
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }

            $filename = 'company_' . Str::uuid() . '.' .
                        $request->file('logo')->extension();

            $path = $request->file('logo')
                ->storeAs('companies', $filename, 'public');

            $validated['logo'] = $path;
        } else {
            // Ne pas écraser le logo existant si aucun nouveau fichier
            unset($validated['logo']);
        }

        /*
        |----------------------------------------------------------------------
        | UPDATE DATA
        |----------------------------------------------------------------------
        */
        $company->update($validated);

        /*
        |----------------------------------------------------------------------
        | CLEAR CACHE (seulement l'ID — jamais l'objet Eloquent)
        |----------------------------------------------------------------------
        */
        Cache::forget('company_id');

        return back()->with(
            'success',
            'Informations de l\'entreprise mises à jour avec succès.'
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
            'invoice_counter' => 1,
        ]);

        Cache::forget('company_id');

        return back()->with(
            'success',
            'Compteur de factures réinitialisé.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE: GET OR CREATE COMPANY (ERP Mono Mode)
    | On cache uniquement l'ID (scalaire), jamais l'objet Eloquent entier.
    | Ainsi, après un update(), la relecture en DB retourne toujours les
    | données fraîches (logo inclus).
    |--------------------------------------------------------------------------
    */
    private function getCompany(): Company
    {
        $id = Cache::rememberForever('company_id', function () {

            $company = Company::first() ?? Company::create([
                'name'            => 'MauriERP',
                'country'         => 'Mauritanie',
                'currency'        => 'MRU',
                'invoice_prefix'  => 'FAC',
                'invoice_format'  => '{prefix}-{number}',
                'invoice_counter' => 1,
                'default_vat'     => 0,
                'is_active'       => true,
            ]);

            return $company->id;
        });

        return Company::findOrFail($id);
    }
}