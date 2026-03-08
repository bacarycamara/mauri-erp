<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Models\Company;
use App\Models\AuditLog;
use App\Observers\AuditObserver;
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('company', function () {
            return Cache::rememberForever('company_singleton', fn () => Company::first());
        });
    }

    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PARTAGE DES DONNÉES GLOBALES AUX VUES
        |--------------------------------------------------------------------------
        */
        if (!app()->runningInConsole()) {
            View::composer([
                'layouts.app',
                'layouts.guest',
                'layouts.partials.*',
            ], function ($view) {
                $company = Cache::rememberForever('company_singleton', function () {
                    return Company::first();
                });
                $view->with('company', $company);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | AUDIT OBSERVER — enregistré TOUJOURS (pas de condition auth)
        |--------------------------------------------------------------------------
        | L'observer lui-même utilise Auth::id() qui retourne null si non connecté
        | On enregistre l'observer au boot, pas besoin d'attendre l'auth
        */
        if ($this->app->runningInConsole()) {
            return;
        }

        //  Enregistrer l'observer sur tous les modèles SANS condition auth
        static $loaded = false;
        if ($loaded) return;
        $loaded = true;

        foreach (File::files(app_path('Models')) as $file) {
            $class = 'App\\Models\\' . pathinfo($file, PATHINFO_FILENAME);
            if (class_exists($class) && is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class)) {
                $class::observe(AuditObserver::class);
            }
        }

        //  Login / Logout
        Event::listen(Login::class, function (Login $event) {
            try {
                AuditLog::create([
                    'user_id'    => $event->user->id,
                    'action'     => 'login',
                    'model_type' => 'User',
                    'model_id'   => $event->user->id,
                    'old_values' => null,
                    'new_values' => ['email' => $event->user->email],
                    'ip_address' => Request::ip(),
                    'user_agent' => Request::userAgent(),
                ]);
            } catch (\Throwable $e) {}
        });

        Event::listen(Logout::class, function (Logout $event) {
            if (!$event->user) return;
            try {
                AuditLog::create([
                    'user_id'    => $event->user->id,
                    'action'     => 'logout',
                    'model_type' => 'User',
                    'model_id'   => $event->user->id,
                    'old_values' => null,
                    'new_values' => ['email' => $event->user->email],
                    'ip_address' => Request::ip(),
                    'user_agent' => Request::userAgent(),
                ]);
            } catch (\Throwable $e) {}
        });
    }
}