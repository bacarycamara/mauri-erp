<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'nif',
        'rc',
        'address',
        'city',
        'country',
        'opening_balance',
        'current_balance',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query)
    {
        return $query->where('is_active', false);
    }

    public function scopeDebtors(Builder $query)
    {
        return $query->where('current_balance', '>', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | ERP SEARCH
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(Builder $query, $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('nif', 'like', "%{$term}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTHODES FINANCIÈRES
    |--------------------------------------------------------------------------
    */

    public function increaseBalance($amount)
    {
        $this->increment('current_balance', round($amount,2));
    }

    public function decreaseBalance($amount)
    {
        $this->decrement('current_balance', round($amount,2));
    }

    /*
    |--------------------------------------------------------------------------
    | RECALCUL DETTE CLIENT (SÉCURITÉ ERP)
    |--------------------------------------------------------------------------
    */

    public function recalculateBalance()
    {
        $totalDue = $this->sales()
            ->whereIn('status', ['confirmed','partial'])
            ->sum('due_amount');

        $this->update([
            'current_balance' => round($totalDue,2)
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STATISTIQUES CLIENT
    |--------------------------------------------------------------------------
    */

    public function getTotalSalesAttribute()
    {
        return (float) $this->sales()->sum('total_amount');
    }

    public function getTotalPaidAttribute()
    {
        return (float) $this->sales()->sum('paid_amount');
    }

    public function getTotalDueAttribute()
    {
        return (float) $this->sales()->sum('due_amount');
    }

    public function getSalesCountAttribute()
    {
        return $this->sales()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFormattedBalanceAttribute()
    {
        return number_format($this->current_balance, 2) . ' ' . company()?->currency;
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active
            ? 'bg-green-100 text-green-700'
            : 'bg-gray-200 text-gray-600';
    }

    public function getDebtBadgeAttribute()
    {
        return $this->current_balance > 0
            ? 'bg-red-100 text-red-700'
            : 'bg-green-100 text-green-700';
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIQUE MÉTIER ERP
    |--------------------------------------------------------------------------
    */

    public function canBeDeleted()
    {
        return !$this->sales()->exists();
    }
}