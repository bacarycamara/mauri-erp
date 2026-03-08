<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'category',
        'expense_date',
        'amount',
        'payment_method',
        'cash_register_id',
        'status',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'approved_at'  => 'datetime',
        'amount'       => 'decimal:2',
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

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transaction()
    {
        return $this->hasOne(CashTransaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(Builder $query, $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('reference', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    public function scopeStatus(Builder $query, $status)
    {
        if (!$status) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeBetweenDates(Builder $query, $from, $to)
    {
        if ($from && $to) {
            $query->whereBetween('expense_date', [$from, $to]);
        }

        return $query;
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
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

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'pending'   => '<span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">En attente</span>',
            'approved'  => '<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Approuvée</span>',
            'cancelled' => '<span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Annulée</span>',
            default     => '<span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">Inconnu</span>',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT LOGIC
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        /*
        |--------------------------------------------------------------------------
        | CREATING
        |--------------------------------------------------------------------------
        */

        static::creating(function ($expense) {

            DB::transaction(function () use ($expense) {

                if (empty($expense->reference)) {

                    $lastId = static::withTrashed()->max('id') ?? 0;
                    $next   = $lastId + 1;

                    $expense->reference =
                        'EXP-' . str_pad($next, 6, '0', STR_PAD_LEFT);
                }

                $expense->status ??= 'pending';

                if ($expense->amount <= 0) {
                    throw new \Exception("Montant invalide.");
                }

                if ($expense->status === 'approved') {
                    self::processApproval($expense);
                }
            });
        });

        /*
        |--------------------------------------------------------------------------
        | UPDATED
        |--------------------------------------------------------------------------
        */

        static::updated(function ($expense) {

            if ($expense->isDirty('status')) {

                DB::transaction(function () use ($expense) {

                    if ($expense->status === 'approved') {
                        self::processApproval($expense);
                    }

                    if ($expense->status === 'cancelled') {

                        if ($expense->transaction) {
                            $expense->transaction->delete();
                        }

                        $expense->approved_by = null;
                        $expense->approved_at = null;

                        $expense->saveQuietly();
                    }
                });
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVAL PROCESS
    |--------------------------------------------------------------------------
    */

    protected static function processApproval($expense)
    {
        $cash = CashRegister::lockForUpdate()
            ->findOrFail($expense->cash_register_id);

        if (!$cash->isOpen()) {
            throw new \Exception("La caisse est fermée.");
        }

        if ($expense->amount > $cash->current_balance) {
            throw new \Exception("Solde insuffisant dans la caisse.");
        }

        $expense->approved_by = Auth::id();
        $expense->approved_at = now();

        $expense->saveQuietly();

        CashTransaction::create([
            'cash_register_id' => $expense->cash_register_id,
            'type'             => 'out',
            'amount'           => round($expense->amount,2),
            'description'      => 'Dépense ' . $expense->reference,
            'source'           => 'expense',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}