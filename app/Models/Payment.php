<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Payment extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | CONSTANTS (ERP SAFE)
    |--------------------------------------------------------------------------
    */
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_IN  = 'in';
    public const TYPE_OUT = 'out';

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'payment_number',
        'purchase_id',
        'sale_id',
        'cash_register_id',
        'user_id', 
        'type',
        'amount',
        'payment_method',
        'payment_provider',
        'status',
        'payment_date',
        'reference',
        'notes',
        
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function transaction()
    {
        return $this->hasOne(CashTransaction::class);
    }

    /**
     *  Caissier (impression thermique)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeConfirmed(Builder $query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeIncoming(Builder $query)
    {
        return $query->where('type', self::TYPE_IN);
    }

    public function scopeOutgoing(Builder $query)
    {
        return $query->where('type', self::TYPE_OUT);
    }

    public function scopeSearch(Builder $query, $search)
    {
        if (!$search) return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('payment_number', 'like', "%{$search}%")
              ->orWhere('reference', 'like', "%{$search}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2).' '.company()?->currency;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        if ($this->payment_provider) {
            return strtoupper($this->payment_provider);
        }

        return match ($this->payment_method) {
            'cash'          => 'ESPÈCES',
            'mobile_money'  => 'MOBILE MONEY',
            'bank_transfer' => 'VIREMENT BANCAIRE',
            'check'         => 'CHÈQUE',
            default         => strtoupper($this->payment_method ?? ''),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | MODEL EVENTS (ERP CORE)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        /*
        |--------------------------------------------------------------------------
        | BEFORE CREATE
        |--------------------------------------------------------------------------
        */
        static::creating(function ($payment) {

            $payment->amount = round((float)$payment->amount, 2);

            if ($payment->amount <= 0) {
                throw new \Exception("Montant paiement invalide.");
            }

            if (!$payment->sale_id && !$payment->purchase_id) {
                throw new \Exception("Aucune vente ou achat lié.");
            }

            if ($payment->sale_id && $payment->type !== self::TYPE_IN) {
                throw new \Exception("Paiement client doit être 'in'.");
            }

            if ($payment->purchase_id && $payment->type !== self::TYPE_OUT) {
                throw new \Exception("Paiement fournisseur doit être 'out'.");
            }

            /*
            |------------------------------------------------------------------
            | AUTO USER (CAISSIER)
            |------------------------------------------------------------------
            */
            if (Auth::check() && empty($payment->user_id)) {
                $payment->user_id = Auth::id();
            }

            /*
            |------------------------------------------------------------------
            | PROVIDER → METHOD
            |------------------------------------------------------------------
            */
            if ($payment->payment_provider) {

                $map = [
                    'masrvi'  => 'mobile_money',
                    'bankily' => 'mobile_money',
                    'sedad'   => 'mobile_money',
                    'click'   => 'mobile_money',

                    'cash' => 'cash',
                    'bank_transfer' => 'bank_transfer',
                    'check' => 'check',
                    'other' => 'other',
                ];

                $payment->payment_method =
                    $map[$payment->payment_provider] ?? 'other';
            }

            /*
            |------------------------------------------------------------------
            | AUTO NUMBER
            |------------------------------------------------------------------
            */
            if (empty($payment->payment_number)) {

                $last = static::withTrashed()->max('id') ?? 0;

                $payment->payment_number =
                    'PAY-' . str_pad($last + 1, 6, '0', STR_PAD_LEFT);
            }

            $payment->status ??= self::STATUS_CONFIRMED;
        });

        /*
        |--------------------------------------------------------------------------
        | AFTER CREATE
        |--------------------------------------------------------------------------
        */
        static::created(function ($payment) {

            if (!$payment->isConfirmed()) {
                return;
            }

            if ($payment->sale) {
                $payment->sale->registerPayment($payment->amount);
            }

            if ($payment->purchase) {
                $payment->purchase->registerPayment($payment->amount);
            }

            if ($payment->cash_register_id) {

                CashTransaction::create([
                    'cash_register_id' => $payment->cash_register_id,
                    'payment_id'       => $payment->id,
                    'type'             => $payment->isIncoming() ? 'in' : 'out',
                    'amount'           => $payment->amount,
                    'description'      =>
                        ($payment->isIncoming()
                            ? 'Encaissement '
                            : 'Décaissement ')
                        .$payment->payment_number,
                ]);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | CANCEL PAYMENT
        |--------------------------------------------------------------------------
        */
        static::updated(function ($payment) {

            if (!$payment->isDirty('status') ||
                $payment->status !== self::STATUS_CANCELLED) {
                return;
            }

            $payment->sale?->calculateTotals();
            $payment->purchase?->calculateTotals();

            $payment->transaction()?->delete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isIncoming(): bool
    {
        return $this->type === self::TYPE_IN;
    }

    public function isOutgoing(): bool
    {
        return $this->type === self::TYPE_OUT;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function canBeCancelled(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    
}