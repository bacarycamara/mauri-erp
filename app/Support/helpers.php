<?php

use Illuminate\Support\Facades\Cache;
use App\Models\Company;

if (!function_exists('company')) {

    /**
     * Retourne l'entreprise active (ERP)
     * Cache intelligent pour améliorer les performances.
     */
    function company(): ?Company
    {
        // Ne rien charger si l'utilisateur n'est pas connecté
        if (!auth()->check()) {
            return null;
        }

        return Cache::remember(
            'company',
            now()->addHours(6), // cache 6 heures
            function () {
                return Company::query()->first();
            }
        );
    }
}