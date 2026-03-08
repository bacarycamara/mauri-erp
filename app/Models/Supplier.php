<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
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

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithDebt($query)
    {
        return $query->where('current_balance', '>', 0);
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

    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO INITIALISATION
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {

            // Initialisation balance
            if ($supplier->current_balance === null) {
                $supplier->current_balance =
                    $supplier->opening_balance ?? 0;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTHODES FINANCIÈRES
    |--------------------------------------------------------------------------
    */

    // Ajouter dette fournisseur
    public function increaseBalance($amount)
    {
        $this->increment('current_balance', round(abs($amount),2));
    }

    // Réduire dette fournisseur
    public function decreaseBalance($amount)
    {
        $this->decrement('current_balance', round(abs($amount),2));
    }

    // Vérifier si dette
    public function hasDebt(): bool
    {
        return $this->current_balance > 0;
    }
}