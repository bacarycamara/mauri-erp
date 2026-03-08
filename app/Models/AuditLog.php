<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeAction(Builder $query, $action): Builder
    {
        return $action
            ? $query->where('action', $action)
            : $query;
    }

    public function scopeModel(Builder $query, $model): Builder
    {
        return $model
            ? $query->where('model_type', $model)
            : $query;
    }

    public function scopeUserFilter(Builder $query, $userId): Builder
    {
        return $userId
            ? $query->where('user_id', $userId)
            : $query;
    }

    public function scopeDateRange(Builder $query, $from, $to): Builder
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getModelNameAttribute(): ?string
    {
        return $this->model_type
            ? class_basename($this->model_type)
            : null;
    }

    public function getActionBadgeAttribute(): string
    {
        return match ($this->action) {

            'created' =>
                '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 font-semibold">Création</span>',

            'updated' =>
                '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-semibold">Modification</span>',

            'deleted' =>
                '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-semibold">Suppression</span>',

            'login' =>
                '<span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold">Connexion</span>',

            default =>
                '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700 font-semibold">'
                . e(Str::ucfirst($this->action))
                . '</span>',
        };
    }

    public function getFormattedDateAttribute(): string
    {
        return optional($this->created_at)->format('d M Y H:i');
    }

    public function getShortUserAgentAttribute(): ?string
    {
        return $this->user_agent
            ? Str::limit($this->user_agent, 40)
            : null;
    }

    public function getHasChangesAttribute(): bool
    {
        return !empty($this->old_values) || !empty($this->new_values);
    }


    /*
    |--------------------------------------------------------------------------
    | HELPER STATIC LOGGER
    |--------------------------------------------------------------------------
    */
    public static function log(
        string $action,
        ?Model $model = null,
        array $old = [],
        array $new = []
    ): void {
        self::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id'   => $model?->id,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}