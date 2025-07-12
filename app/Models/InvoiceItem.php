<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'good_id',
        'item_name',
        'item_code',
        'quantity',
        'unit_price',
        'total_amount',
        'tax_amount',
        'uom',
        'tax_category',
        'tax_rate',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns this item.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    /**
     * Get the good that owns this item.
     */
    public function good()
    {
        return $this->belongsTo(EfrisGood::class, 'good_id', 'eg_id');
    }

    /**
     * Calculate item totals.
     */
    public function calculateTotals()
    {
        $this->total_amount = $this->quantity * $this->unit_price;
        
        // Calculate tax based on category
        if ($this->tax_category === 'V') {
            $this->tax_amount = $this->total_amount * ($this->tax_rate / 100);
        } else {
            $this->tax_amount = 0;
        }
        
        $this->save();
    }

    /**
     * Get the tax category display name.
     */
    public function getTaxCategoryNameAttribute()
    {
        return match($this->tax_category) {
            'V' => 'VAT',
            'Z' => 'Zero Rated',
            'E' => 'Exempt',
            'D' => 'Deemed',
            default => $this->tax_category,
        };
    }
} 