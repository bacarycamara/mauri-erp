<?php

use Illuminate\Support\Facades\Cache;
use App\Models\Company;

if (! function_exists('company')) {
    /**
     * Retourne l'entreprise active (ERP mono-instance).
     * On cache uniquement l'ID (scalaire) — jamais l'objet Eloquent —
     * pour que chaque requête reçoive toujours les données fraîches (logo inclus).
     */
    function company(): ?Company
    {
        if (! auth()->check()) {
            return null;
        }

        $id = Cache::rememberForever('company_id', function () {
            return Company::first()?->id;
        });

        return $id ? Company::find($id) : null;
    }
}