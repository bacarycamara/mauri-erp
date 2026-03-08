<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'unit_price',
        'vat_rate',
        'discount_rate',
        'subtotal',
        'vat_amount',
        'discount_amount',
        'total',
    ];

    protected $casts = [
        'quantity'        => 'decimal:2',
        'unit_price'      => 'decimal:2',
        'vat_rate'        => 'decimal:2',
        'discount_rate'   => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'vat_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total'           => 'decimal:2',
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT - CALCUL ERP AUTOMATIQUE
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        //  Calcul automatique avant sauvegarde
        static::saving(function ($item) {

            // Sécurité ERP
            $item->quantity      = max(0, $item->quantity);
            $item->unit_price    = max(0, $item->unit_price);
            $item->vat_rate      = max(0, $item->vat_rate ?? 0);
            $item->discount_rate = max(0, $item->discount_rate ?? 0);

            $item->subtotal = $item->quantity * $item->unit_price;

            $item->vat_amount =
                ($item->subtotal * $item->vat_rate) / 100;

            $item->discount_amount =
                ($item->subtotal * $item->discount_rate) / 100;

            $item->total =
                $item->subtotal
                + $item->vat_amount
                - $item->discount_amount;
        });

        //  Après création → recalcul achat
        static::created(function ($item) {
            $item->purchase->calculateTotals();
        });

        //  Après modification → recalcul achat
        static::updated(function ($item) {
            $item->purchase->calculateTotals();
        });

        //  Après suppression → recalcul achat
        static::deleted(function ($item) {
            $item->purchase->calculateTotals();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2) . ' ' . company()?->currency;
    }

    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 2) . ' ' . company()?->currency;
    }
}