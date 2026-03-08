<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cash_register_id',
        'payment_id',
        'user_id',
        'type',        // in / out
        'amount',
        'reference',
        'description',
        'source',      // payment / manual / adjustment
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeIncoming($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOutgoing($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeManual($query)
    {
        return $query->where('source', 'manual');
    }

    public function scopeFromPayment($query)
    {
        return $query->where('source', 'payment');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2) . ' ' . company()?->currency;
    }

    public function getSourceLabelAttribute()
    {
        return match ($this->source) {
            'payment' => 'Paiement',
            'manual' => 'Manuel',
            'adjustment' => 'Ajustement',
            default => '—'
        };
    }

    /*
    |--------------------------------------------------------------------------
    | ERP AUTOMATION LOGIC
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        /*
        |--------------------------------------------------------------------------
        | BEFORE CREATE
        |--------------------------------------------------------------------------
        */
        static::creating(function ($transaction) {

            $cash = CashRegister::lockForUpdate()
                ->findOrFail($transaction->cash_register_id);

            if (!$cash->isOpen()) {
                throw new \Exception(
                    "Impossible d'enregistrer une transaction : caisse fermée."
                );
            }

            if ($transaction->amount <= 0) {
                throw new \Exception('Le montant doit être supérieur à 0.');
            }

            if ($transaction->type === 'out') {
                if ($transaction->amount > $cash->current_balance) {
                    throw new \Exception('Solde insuffisant dans la caisse.');
                }
            }

            // Auto user
            if (empty($transaction->user_id) && Auth::check()) {
                $transaction->user_id = Auth::id();
            }

            // Auto reference
            if (empty($transaction->reference)) {
                $transaction->reference =
                    'TX-' . now()->format('YmdHis') . '-' . strtoupper(uniqid());
            }

            // Default source
            if (empty($transaction->source)) {
                $transaction->source = 'manual';
            }
        });

        /*
        |--------------------------------------------------------------------------
        | AFTER CREATE
        |--------------------------------------------------------------------------
        */
        static::created(function ($transaction) {

            $cash = $transaction->cashRegister;

            if (!$cash) {
                return;
            }

            if ($transaction->type === 'in') {
                $cash->increment('total_in', $transaction->amount);
            }

            if ($transaction->type === 'out') {
                $cash->increment('total_out', $transaction->amount);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | SOFT DELETE
        |--------------------------------------------------------------------------
        */
        static::deleted(function ($transaction) {

            if ($transaction->isForceDeleting()) {
                return;
            }

            $cash = $transaction->cashRegister;

            if (!$cash) {
                return;
            }

            if ($transaction->type === 'in') {
                $cash->decrement('total_in', $transaction->amount);
            }

            if ($transaction->type === 'out') {
                $cash->decrement('total_out', $transaction->amount);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isIncoming(): bool
    {
        return $this->type === 'in';
    }

    public function isOutgoing(): bool
    {
        return $this->type === 'out';
    }
}