<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'reference',
        'purchase_date',
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
        'purchase_date'   => 'date',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
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
        return $query->whereIn('status', ['draft', 'partial']);
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT - RÉFÉRENCE AUTO
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {

            if (empty($purchase->reference)) {

                $prefix = 'ACH-' . now()->format('Y');

                $last = self::where('reference', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->first();

                $next = $last
                    ? ((int) substr($last->reference, -4)) + 1
                    : 1;

                $purchase->reference =
                    $prefix . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
            }

            if (empty($purchase->purchase_date)) {
                $purchase->purchase_date = now();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CALCUL TOTALS
    |--------------------------------------------------------------------------
    */

    public function calculateTotals()
    {
        $this->loadMissing('items');

        $this->subtotal        = round($this->items->sum('subtotal'), 2);
        $this->vat_amount      = round($this->items->sum('vat_amount'), 2);
        $this->discount_amount = round($this->items->sum('discount_amount'), 2);
        $this->total_amount    = round($this->items->sum('total'), 2);

        $this->due_amount = max(
            0,
            round($this->total_amount - $this->paid_amount, 2)
        );

        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRMATION ACHAT
    |--------------------------------------------------------------------------
    */

    public function confirm()
    {
        if (!in_array($this->status, ['draft', 'partial'])) {
            return;
        }

        DB::transaction(function () {

            $this->loadMissing(['items.product', 'supplier']);

            if ($this->items->isEmpty()) {
                throw new \Exception("Impossible de confirmer un achat sans articles.");
            }

            foreach ($this->items as $item) {

                $product = $item->product;

                if (!$product) {
                    throw new \Exception("Produit introuvable.");
                }

                $stockBefore = $product->stock_quantity;

                $product->increment('stock_quantity', $item->quantity);

                $stockAfter = $stockBefore + $item->quantity;

                StockMovement::create([
                    'product_id'   => $product->id,
                    'type'         => 'purchase',
                    'quantity'     => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'reference'    => $this->reference,
                ]);
            }

            if ($this->due_amount > 0) {
                $this->supplier?->increaseBalance($this->due_amount);
            }

            $this->status = $this->due_amount > 0 ? 'confirmed' : 'paid';

            $this->save();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ENREGISTRER PAIEMENT
    |--------------------------------------------------------------------------
    */

    public function registerPayment($amount)
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($amount) {

            if ($amount > $this->due_amount) {
                throw new \Exception("Montant supérieur au reste à payer.");
            }

            $this->paid_amount += $amount;

            $this->due_amount = max(
                0,
                round($this->total_amount - $this->paid_amount, 2)
            );

            $this->status = $this->due_amount == 0 ? 'paid' : 'partial';

            $this->supplier?->decreaseBalance($amount);

            $this->save();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount, 2) . ' ' . company()?->currency;
    }

    public function getFormattedDueAttribute()
    {
        return number_format($this->due_amount, 2) . ' ' . company()?->currency;
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'draft'     => '<span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Brouillon</span>',
            'confirmed' => '<span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">Confirmé</span>',
            'partial'   => '<span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Partiel</span>',
            'paid'      => '<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">Payé</span>',
            'cancelled' => '<span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs">Annulé</span>',
            default     => '<span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Inconnu</span>',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | ERP HELPER
    |--------------------------------------------------------------------------
    */

    public function getRealDueAmount(): float
    {
        return (float) $this->due_amount;
    }
}