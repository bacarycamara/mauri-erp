<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        // ── CREATED ──────────────────────────────────────────
        static::created(function ($model) {
            AuditLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'created',
                'model_type' => class_basename($model),
                'model_id'   => $model->getKey(),
                'old_values' => null,
                'new_values' => $model->getAuditableAttributes(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });

        // ── UPDATED ──────────────────────────────────────────
        static::updated(function ($model) {
            $dirty = $model->getDirty();

            // Ignorer les champs non pertinents
            $ignore = ['updated_at', 'remember_token', 'email_verified_at'];
            $dirty  = array_diff_key($dirty, array_flip($ignore));

            if (empty($dirty)) return;

            AuditLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'updated',
                'model_type' => class_basename($model),
                'model_id'   => $model->getKey(),
                'old_values' => array_intersect_key($model->getOriginal(), $dirty),
                'new_values' => $dirty,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });

        // ── DELETED ──────────────────────────────────────────
        static::deleted(function ($model) {
            AuditLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'deleted',
                'model_type' => class_basename($model),
                'model_id'   => $model->getKey(),
                'old_values' => $model->getAuditableAttributes(),
                'new_values' => null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });
    }

    /**
     * Retourne les attributs à logger (exclut les champs sensibles)
     */
    protected function getAuditableAttributes(): array
    {
        $hidden  = array_merge($this->hidden ?? [], ['password', 'remember_token', 'two_factor_secret']);
        $fillable = $this->getFillable();

        return array_diff_key(
            $this->getAttributes(),
            array_flip(array_merge($hidden, ['created_at', 'updated_at']))
        );
    }
}