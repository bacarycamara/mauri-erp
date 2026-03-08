<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditObserver
{
    /*
    |--------------------------------------------------------------------------
    | Modèles à NE PAS tracer (évite la récursion infinie)
    |--------------------------------------------------------------------------
    */
    private array $excluded = [
        'AuditLog',
        'PersonalAccessToken',
        'PasswordResetToken',
        'Session',
    ];

    private function shouldSkip(Model $model): bool
    {
        return in_array(class_basename($model), $this->excluded);
    }

    private function getAttributes(Model $model): array
    {
        $hidden = array_merge(
            $model->getHidden(),
            ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes']
        );

        return array_diff_key(
            $model->getAttributes(),
            array_flip(array_merge($hidden, ['created_at', 'updated_at']))
        );
    }

    private function log(string $action, Model $model, ?array $old, ?array $new): void
    {
        try {
            AuditLog::create([
                'user_id'    => Auth::id(),
                'action'     => $action,
                'model_type' => class_basename($model),
                'model_id'   => $model->getKey(),
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Ne jamais bloquer l'opération principale
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CREATED
    |--------------------------------------------------------------------------
    */
    public function created(Model $model): void
    {
        if ($this->shouldSkip($model)) return;

        $this->log('created', $model, null, $this->getAttributes($model));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATED
    |--------------------------------------------------------------------------
    */
    public function updated(Model $model): void
    {
        if ($this->shouldSkip($model)) return;

        $dirty  = $model->getDirty();
        $ignore = ['updated_at', 'remember_token', 'email_verified_at', 'last_seen_at'];
        $dirty  = array_diff_key($dirty, array_flip($ignore));

        if (empty($dirty)) return;

        $this->log(
            'updated',
            $model,
            array_intersect_key($model->getOriginal(), $dirty),
            $dirty
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETED
    |--------------------------------------------------------------------------
    */
    public function deleted(Model $model): void
    {
        if ($this->shouldSkip($model)) return;

        $this->log('deleted', $model, $this->getAttributes($model), null);
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORED (soft delete)
    |--------------------------------------------------------------------------
    */
    public function restored(Model $model): void
    {
        if ($this->shouldSkip($model)) return;

        $this->log('restored', $model, null, $this->getAttributes($model));
    }
}