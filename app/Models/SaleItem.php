<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
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

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT METHOD (ERP LOGIC)
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {

            //  Sécurités
            if ($item->quantity <= 0) {
                throw new \Exception("Quantité invalide");
            }

            if ($item->unit_price < 0) {
                throw new \Exception("Prix invalide");
            }

            //  Auto récupérer prix produit si non fourni
            if (empty($item->unit_price) && $item->product) {
                $item->unit_price = $item->product->selling_price;
            }

            $subtotal = $item->quantity * $item->unit_price;

            $vatAmount = ($subtotal * $item->vat_rate) / 100;

            $discountAmount = ($subtotal * $item->discount_rate) / 100;

            $total = $subtotal + $vatAmount - $discountAmount;

            $item->subtotal        = round($subtotal, 2);
            $item->vat_amount      = round($vatAmount, 2);
            $item->discount_amount = round($discountAmount, 2);
            $item->total           = round($total, 2);
        });

        /*
        |--------------------------------------------------------------------------
        | AUTO RECALCULATE SALE TOTALS
        |--------------------------------------------------------------------------
        */

        static::saved(function ($item) {
            if ($item->sale) {
                $item->sale->calculateTotals();
            }
        });

        static::deleted(function ($item) {
            if ($item->sale) {
                $item->sale->calculateTotals();
            }
        });
    }
}