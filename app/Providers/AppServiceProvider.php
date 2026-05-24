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
    /*
    |--------------------------------------------------------------------------
    | REGISTER
    | On cache uniquement l'ID (scalaire), jamais l'objet Eloquent.
    | Le singleton résout toujours une instance fraîche depuis la DB.
    |--------------------------------------------------------------------------
    */
    public function register(): void
    {
        $this->app->singleton('company', function () {
            $id = Cache::rememberForever('company_id', fn () => Company::first()?->id);
            return $id ? Company::find($id) : null;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    */
    public function boot(): void
    {
        /*
        |----------------------------------------------------------------------
        | PARTAGE DES DONNÉES GLOBALES AUX VUES
        | On relit la DB à chaque requête via le singleton (pas de cache objet).
        |----------------------------------------------------------------------
        */
        if (!app()->runningInConsole()) {
            View::composer([
                'layouts.app',
                'layouts.guest',
                'layouts.partials.*',
            ], function ($view) {
                // app('company') résout le singleton ci-dessus → toujours frais
                $view->with('company', app('company'));
            });
        }

        /*
        |----------------------------------------------------------------------
        | AUDIT OBSERVER
        |----------------------------------------------------------------------
        */
        if ($this->app->runningInConsole()) {
            return;
        }

        static $loaded = false;
        if ($loaded) return;
        $loaded = true;

        foreach (File::files(app_path('Models')) as $file) {
            $class = 'App\\Models\\' . pathinfo($file, PATHINFO_FILENAME);
            if (
                class_exists($class) &&
                is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class)
            ) {
                $class::observe(AuditObserver::class);
            }
        }

        /*
        |----------------------------------------------------------------------
        | LOGIN / LOGOUT AUDIT
        |----------------------------------------------------------------------
        */
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