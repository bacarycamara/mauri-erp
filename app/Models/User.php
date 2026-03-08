<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Spatie Guard
    |--------------------------------------------------------------------------
    */
    protected $guard_name = 'web';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status', // active / inactive
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'deleted_at'        => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT (Default Status)
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->status)) {
                $user->status = 'active';
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function openedCashRegisters()
    {
        return $this->hasMany(CashRegister::class, 'opened_by');
    }

    public function closedCashRegisters()
    {
        return $this->hasMany(CashRegister::class, 'closed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeSearch(Builder $query, $search)
    {
        if (!$search) return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getRoleNameAttribute(): string
    {
        return $this->roles->pluck('name')->implode(', ');
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'active'
            ? '<span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700 font-semibold">Actif</span>'
            : '<span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-700 font-semibold">Inactif</span>';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('Gestionnaire');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('Caissier');
    }

    /*
    |--------------------------------------------------------------------------
    | ERP SECURITY
    |--------------------------------------------------------------------------
    */

    public function canLogin(): bool
    {
        return $this->isActive() && !$this->trashed();
    }

    public function block(): void
    {
        $this->update(['status' => 'inactive']);
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    public function canBeDeleted(): bool
    {
        // Empêcher suppression si caisse ouverte
        if ($this->openedCashRegisters()->where('status', 'open')->exists()) {
            return false;
        }

        return true;
    }
}