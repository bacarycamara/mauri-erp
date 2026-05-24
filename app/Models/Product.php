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
        |----------------------------------------------------------------------
        | SKU AUTO — corrigé pour UTF-8 (caractères accentués : é, à, ç…)
        |----------------------------------------------------------------------
        */
        static::creating(function ($product) {

            if (empty($product->sku)) {

                // ✅ Préfixe : 3 CARACTÈRES (mb_substr) en majuscules (mb_strtoupper)
                if ($product->category_id) {
                    $category = Category::select('name')->find($product->category_id);
                    $prefix   = $category
                        ? mb_strtoupper(mb_substr($category->name, 0, 3, 'UTF-8'), 'UTF-8')
                        : 'PRO';
                } else {
                    $prefix = 'PRO';
                }

                // ✅ Longueur du préfixe en caractères (pas en bytes)
                $prefixLen = mb_strlen($prefix, 'UTF-8');

                // ✅ Inclure les soft-deleted pour éviter les conflits
                $lastSku = self::withTrashed()
                    ->where('sku', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->value('sku');

                $nextNumber = 1;
                if ($lastSku) {
                    // ✅ mb_substr pour extraire la partie numérique correctement
                    $numeric    = (int) mb_substr($lastSku, $prefixLen, null, 'UTF-8');
                    $nextNumber = $numeric + 1;
                }

                // ✅ Boucle anti-collision : incrémente jusqu'à trouver un SKU libre
                do {
                    $sku    = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                    $exists = self::withTrashed()->where('sku', $sku)->exists();
                    $nextNumber++;
                } while ($exists);

                $product->sku = $sku;
            }
        });

        /*
        |----------------------------------------------------------------------
        | CALCUL MARGE AUTOMATIQUE
        |----------------------------------------------------------------------
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