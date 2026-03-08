<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'reference',
        'sale_date',
        'subtotal',
        'vat_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'sale_date'       => 'date',
        'subtotal'        => 'decimal:2',
        'vat_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'due_amount'      => 'decimal:2',
    ];

    protected $attributes = [
        'status'      => 'draft',
        'paid_amount' => 0,
        'due_amount'  => 0,
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft','partial']);
    }

    /*
    |--------------------------------------------------------------------------
    | FINANCIAL CALCULATIONS
    |--------------------------------------------------------------------------
    */

    public function getRealPaidAmount(): float
    {
        return round((float) $this->payments()->sum('amount'), 2);
    }

    public function getRealDueAmount(): float
    {
        return round(
            max(
                0,
                round($this->total_amount,2) - $this->getRealPaidAmount()
            ),
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CALCUL TOTALS
    |--------------------------------------------------------------------------
    */

    public function calculateTotals()
    {
        $this->loadMissing('items');

        $this->subtotal        = round($this->items->sum('subtotal'),2);
        $this->vat_amount      = round($this->items->sum('vat_amount'),2);
        $this->discount_amount = round($this->items->sum('discount_amount'),2);
        $this->total_amount    = round($this->items->sum('total'),2);

        $this->paid_amount = $this->getRealPaidAmount();
        $this->due_amount  = $this->getRealDueAmount();

        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRM SALE
    |--------------------------------------------------------------------------
    */

    public function confirm()
    {
        if (in_array($this->status, ['confirmed','paid'])) {
            return;
        }

        DB::transaction(function () {

            $this->loadMissing('items.product');

            foreach ($this->items as $item) {

                $product = $item->product;

                if (!$product) {
                    throw new \Exception("Un produit de cette vente n'existe plus.");
                }

                if ($product->stock_quantity <= 0) {
                    throw new \Exception("Produit épuisé : {$product->name}");
                }

                if ($product->stock_quantity < $item->quantity) {
                    throw new \Exception(
                        "Stock insuffisant pour {$product->name} (Disponible : {$product->stock_quantity})"
                    );
                }

                $stockBefore = $product->stock_quantity;

                $product->decrement('stock_quantity', $item->quantity);

                StockMovement::create([
                    'product_id'   => $product->id,
                    'type'         => 'sale',
                    'quantity'     => $item->quantity,
                    'reference'    => $this->reference,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockBefore - $item->quantity,
                ]);
            }

            $realDue = $this->getRealDueAmount();

            if ($realDue > 0) {
                $this->customer?->increaseBalance($realDue);
            }

            $this->status = $realDue == 0 ? 'paid' : 'confirmed';

            $this->save();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER PAYMENT
    |--------------------------------------------------------------------------
    */

    public function registerPayment($amount, $cashRegister = null)
    {
        DB::transaction(function () use ($amount) {

            $sale = self::where('id',$this->id)
                ->lockForUpdate()
                ->first();

            if (!in_array($sale->status,['confirmed','partial'])) {
                throw new \Exception("La vente doit être confirmée avant paiement.");
            }

            $sale->paid_amount = $sale->getRealPaidAmount();
            $sale->due_amount  = $sale->getRealDueAmount();

            $sale->status = $sale->due_amount == 0
                ? 'paid'
                : 'partial';

            $sale->customer?->decreaseBalance($amount);

            $sale->save();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount,2).' '.company()?->currency;
    }

    public function getFormattedDueAttribute()
    {
        return number_format($this->due_amount,2).' '.company()?->currency;
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'draft'     => 'bg-gray-200 text-gray-700',
            'confirmed' => 'bg-blue-100 text-blue-700',
            'partial'   => 'bg-yellow-100 text-yellow-700',
            'paid'      => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default     => 'bg-gray-100 text-gray-600',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO REFERENCE
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {

            if (empty($sale->reference)) {
                $sale->reference =
                    'INV-'.now()->format('Y').'-'.strtoupper(Str::random(4));
            }

            if (empty($sale->sale_date)) {
                $sale->sale_date = now();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK CHECK
    |--------------------------------------------------------------------------
    */

    public function getNeedsRestockAttribute(): bool
    {
        $this->loadMissing('items.product');

        foreach ($this->items as $item) {

            if (!$item->product) continue;

            if ($item->product->type !== 'physical') continue;

            if ($item->product->stock_quantity < $item->quantity) {
                return true;
            }
        }

        return false;
    }

    public function getRestockItemsAttribute()
    {
        $this->loadMissing('items.product');

        return $this->items->filter(function ($item) {

            return $item->product
                && $item->product->type === 'physical'
                && $item->product->stock_quantity < $item->quantity;

        });
    }
}