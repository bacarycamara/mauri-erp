<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CashRegister extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'opening_balance',
        'closing_balance',
        'total_in',
        'total_out',
        'status',
        'opened_by',
        'closed_by',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'total_in'        => 'decimal:2',
        'total_out'       => 'decimal:2',
        'opened_at'       => 'datetime',
        'closed_at'       => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function transactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public static function current()
    {
        return self::open()->first();
    }

    /*
    |--------------------------------------------------------------------------
    | OUVERTURE CAISSE
    |--------------------------------------------------------------------------
    */

    public static function openRegister($openingBalance = 0, $userId = null, $name = 'Caisse principale')
    {
        return DB::transaction(function () use ($openingBalance, $userId, $name) {

            if (self::open()->exists()) {
                throw new \Exception('Une caisse est déjà ouverte.');
            }

            return self::create([
                'name'            => $name,
                'opening_balance' => $openingBalance,
                'closing_balance' => $openingBalance,
                'total_in'        => 0,
                'total_out'       => 0,
                'status'          => 'open',
                'opened_by'       => $userId,
                'opened_at'       => now(),
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | FERMETURE CAISSE
    |--------------------------------------------------------------------------
    */

    public function closeRegister($userId = null)
    {
        if ($this->isClosed()) {
            throw new \Exception('Cette caisse est déjà fermée.');
        }

        return DB::transaction(function () use ($userId) {

            $this->total_in  = $this->transactions()->where('type', 'in')->sum('amount');
            $this->total_out = $this->transactions()->where('type', 'out')->sum('amount');

            $this->closing_balance =
                $this->opening_balance
                + $this->total_in
                - $this->total_out;

            $this->status    = 'closed';
            $this->closed_by = $userId;
            $this->closed_at = now();

            $this->save();

            return $this;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | MOUVEMENTS MANUELS
    |--------------------------------------------------------------------------
    */

    public function deposit($amount)
    {
        if (!$this->isOpen()) {
            throw new \Exception('La caisse est fermée.');
        }

        $this->total_in += $amount;
        $this->closing_balance += $amount;
        $this->save();
    }

    public function withdraw($amount)
    {
        if (!$this->isOpen()) {
            throw new \Exception('La caisse est fermée.');
        }

        if ($this->closing_balance < $amount) {
            throw new \Exception('Solde insuffisant.');
        }

        $this->total_out += $amount;
        $this->closing_balance -= $amount;
        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getCurrentBalanceAttribute()
    {
        return $this->opening_balance + $this->total_in - $this->total_out;
    }

    public function getFormattedBalanceAttribute()
    {
        return number_format($this->current_balance, 2) . ' ' . company()?->currency;
    }

    /*
    |--------------------------------------------------------------------------
    | RAPPORT CAISSE
    |--------------------------------------------------------------------------
    */

    public function getSummary()
    {
        return [
            'opening_balance' => $this->opening_balance,
            'total_in'        => $this->total_in,
            'total_out'       => $this->total_out,
            'closing_balance' => $this->closing_balance,
            'transactions'    => $this->transactions()->count(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PROTECTION SUPPRESSION
    |--------------------------------------------------------------------------
    */

    public function canBeDeleted(): bool
    {
        return !$this->transactions()->exists();
    }
}