<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EfrisGood extends Model
{
    use HasFactory;

    protected $primaryKey = 'eg_id';

    protected $fillable = [
        'eg_name',
        'eg_code',
        'eg_description',
        'qrcode_path',
        'barcode_path',
        'eg_price',
        'eg_uom',
        'eg_tax_category',
        'eg_tax_rate',
        'eg_active',
        'eg_added_by',
        'eg_date_added',
    ];

    protected $casts = [
        'eg_price' => 'decimal:2',
        'eg_tax_rate' => 'decimal:2',
        'eg_active' => 'boolean',
        'eg_date_added' => 'datetime',
    ];

    protected static function booted()
    {
        static::updating(function ($good) {
            if ($good->isDirty('eg_code')) {
                if ($good->qrcode_path && Storage::disk('public')->exists($good->qrcode_path)) {
                    Storage::disk('public')->delete($good->qrcode_path);
                }
                if ($good->barcode_path && Storage::disk('public')->exists($good->barcode_path)) {
                    Storage::disk('public')->delete($good->barcode_path);
                }
                $good->qrcode_path = null;
                $good->barcode_path = null;
            }
        });
    }

    /**
     * Get the user who added this good.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'eg_added_by');
    }

    /**
     * Get the invoice items for this good.
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, 'good_id', 'eg_id');
    }

    /**
     * Scope to get only active goods.
     */
    public function scopeActive($query)
    {
        return $query->where('eg_active', true);
    }

    /**
     * Scope to search goods by name or code.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('eg_name', 'like', "%{$search}%")
              ->orWhere('eg_code', 'like', "%{$search}%");
        });
    }

    /**
     * Get the tax category display name.
     */
    public function getTaxCategoryNameAttribute()
    {
        return match($this->eg_tax_category) {
            'V' => 'VAT',
            'Z' => 'Zero Rated',
            'E' => 'Exempt',
            'D' => 'Deemed',
            default => $this->eg_tax_category,
        };
    }
} 