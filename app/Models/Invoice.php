<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        'invoice_no',
        'efris_invoice_no',
        'customer_id',
        'buyer_tin',
        'buyer_name',
        'buyer_address',
        'buyer_phone',
        'buyer_email',
        'invoice_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'invoice_type',
        'status',
        'remarks',
        'qr_code',
        'fdn',
        'created_by',
        'invoice_date',
    ];

    protected $casts = [
        'invoice_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'invoice_date' => 'datetime',
    ];

    /**
     * Get the user who created this invoice.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the customer for this invoice.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the items for this invoice.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'invoice_id');
    }

    /**
     * Scope to get only draft invoices.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'DRAFT');
    }

    /**
     * Scope to get only submitted invoices.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'SUBMITTED');
    }

    /**
     * Scope to get only approved invoices.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    /**
     * Scope to filter by invoice type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('invoice_type', $type);
    }

    /**
     * Scope to search invoices by number or buyer name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('invoice_no', 'like', "%{$search}%")
              ->orWhere('buyer_name', 'like', "%{$search}%")
              ->orWhere('efris_invoice_no', 'like', "%{$search}%");
        });
    }

    /**
     * Calculate invoice totals from items.
     */
    public function calculateTotals()
    {
        $items = $this->items;
        $invoiceAmount = $items->sum('total_amount');
        $taxAmount = $items->sum('tax_amount');
        $totalAmount = $invoiceAmount + $taxAmount;

        $this->update([
            'invoice_amount' => $invoiceAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'DRAFT' => 'badge-secondary',
            'SUBMITTED' => 'badge-warning',
            'APPROVED' => 'badge-success',
            'REJECTED' => 'badge-danger',
            'CANCELLED' => 'badge-dark',
            default => 'badge-secondary',
        };
    }
} 