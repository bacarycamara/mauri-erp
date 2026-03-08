<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'email',
        'phone',
        'nif',
        'rc',
        'address',
        'city',
        'country',
        'currency',
        'default_vat',
        'invoice_prefix',
        'invoice_counter',
        'invoice_format',
        'invoice_footer',
        'website',
        'bank_account',
        'is_active',
    ];

    protected $casts = [
        'default_vat' => 'decimal:2',
        'is_active'   => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

 public function getLogoUrlAttribute(): string
{
    if (!$this->logo) {
        return asset('images/default-logo.png');
    }

    return asset('storage/' . $this->logo);
}

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address,
            $this->city,
            $this->country,
        ])->filter()->implode(', ');
    }

    /*
    |--------------------------------------------------------------------------
    | INVOICE NUMBER GENERATION
    |--------------------------------------------------------------------------
    */

    public function generateInvoiceNumber(): string
    {
        $number = str_pad($this->invoice_counter, 6, '0', STR_PAD_LEFT);

        $invoiceNumber = str_replace(
            ['{prefix}', '{number}'],
            [$this->invoice_prefix, $number],
            $this->invoice_format
        );

        $this->increment('invoice_counter');

        return $invoiceNumber;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->is_active;
    }
}