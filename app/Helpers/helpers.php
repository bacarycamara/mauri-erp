<?php

use App\Models\Company;
use Illuminate\Support\Facades\Cache;

if (! function_exists('company')) {
    /**
     * Retourne l'instance Company fraîche depuis la DB.
     * On cache uniquement l'ID (scalaire) pour éviter
     * de figer l'objet Eloquent en cache.
     */
    function company(): ?Company
    {
        $id = Cache::rememberForever('company_id', function () {
            return Company::first()?->id;
        });

        return $id ? Company::find($id) : null;
    }
}