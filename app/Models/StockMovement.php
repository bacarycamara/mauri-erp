<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference',
        'notes',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTHODE ENREGISTRER MOUVEMENT
    |--------------------------------------------------------------------------
    */

 public static function record($product, $type, $quantity, $reference = null)
{
    $before = $product->stock_quantity;

    switch ($type) {

        case 'purchase':
        case 'return':
            $product->increment('stock_quantity', $quantity);
            break;

        case 'sale':
            $product->decrement('stock_quantity', $quantity);
            break;

        case 'adjustment':
            // quantity peut être positif ou négatif
            $product->increment('stock_quantity', $quantity);
            break;
    }

    $after = $product->fresh()->stock_quantity;

    return self::create([
        'product_id'   => $product->id,
        'type'         => $type,
        'quantity'     => $quantity,
        'stock_before' => $before,
        'stock_after'  => $after,
        'reference'    => $reference,
    ]);
}
}