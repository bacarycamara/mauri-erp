<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'photo',
        'category_id',
        'type',
        'unit',
        'description',
        'purchase_price',
        'selling_price',
        'profit_margin',
        'vat_rate',
        'stock_quantity',
        'minimum_stock',
        'supplier_reference',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'profit_margin'  => 'decimal:2',
        'vat_rate'       => 'decimal:2',
        'stock_quantity' => 'integer',
        'minimum_stock'  => 'integer',
        'is_active'      => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
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

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFormattedSellingPriceAttribute()
    {
        $currency = company()?->currency ?? '';

        return number_format($this->selling_price, 2) . ' ' . $currency;
    }

    public function getFormattedPurchasePriceAttribute()
    {
        $currency = company()?->currency ?? '';

        return number_format($this->purchase_price, 2) . ' ' . $currency;
    }

    public function getProfitAttribute()
    {
        return $this->selling_price - $this->purchase_price;
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock_quantity <= 0) {
            return 'rupture';
        }

        if ($this->stock_quantity <= $this->minimum_stock) {
            return 'faible';
        }

        return 'normal';
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIQUE ERP (SKU + MARGE)
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        /*
        |--------------------------------------------------------------------------
        | SKU AUTO
        |--------------------------------------------------------------------------
        */
        static::creating(function ($product) {

            if (empty($product->sku)) {

                // Préfixe basé sur catégorie
                if ($product->category_id) {

                    $category = Category::select('name')
                        ->find($product->category_id);

                    $prefix = $category
                        ? strtoupper(Str::substr($category->name, 0, 3))
                        : 'PRO';

                } else {
                    $prefix = 'PRO';
                }

                // Dernier SKU
                $lastSku = self::where('sku', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->value('sku');

                if ($lastSku) {
                    $number = (int) substr($lastSku, 3);
                    $nextNumber = $number + 1;
                } else {
                    $nextNumber = 1;
                }

                $product->sku = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | CALCUL MARGE
        |--------------------------------------------------------------------------
        */
        static::saving(function ($product) {

            if ($product->purchase_price > 0 && $product->selling_price > 0) {

                $product->profit_margin =
                    (($product->selling_price - $product->purchase_price)
                    / $product->purchase_price) * 100;

            } else {
                $product->profit_margin = 0;
            }
        });
    }
}