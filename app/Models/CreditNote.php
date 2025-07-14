<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditNote extends Model
{
    use HasFactory;

    protected $primaryKey = 'cn_id';

    protected $fillable = [
        'credit_note_no',
        'efris_credit_note_no',
        'customer_id',
        'invoice_id',
        'original_invoice_id', // <-- Added for mass assignment
        'original_invoice_no', // <-- Added for mass assignment
        'buyer_tin',
        'buyer_name',
        'buyer_address',
        'buyer_phone',
        'buyer_email',
        'credit_note_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'credit_note_type',
        'status',
        'remarks',
        'qr_code',
        'fdn',
        'created_by',
        'credit_note_date',
    ];

    protected $casts = [
        'invoice_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'efris_response' => 'array',
    ];

    /**
     * Get the original invoice that this credit note is for.
     */
    public function originalInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'original_invoice_id', 'invoice_id');
    }

    /**
     * Get the items in this credit note.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class, 'credit_note_id', 'cn_id');
    }

    /**
     * Get the user who created this credit note.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the customer for this credit note.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the user who last updated this credit note.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Generate credit note number.
     */
    public static function generateCreditNoteNumber(): string
    {
        $prefix = 'CN';
        $year = date('Y');
        $month = date('m');
        
        $lastCreditNote = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('cn_id', 'desc')
            ->first();
        
        $sequence = $lastCreditNote ? (intval(substr($lastCreditNote->cn_no, -4)) + 1) : 1;
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate totals for the credit note.
     */
    public function calculateTotals(): void
    {
        $this->invoice_amount = $this->items->sum('total_amount');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total_amount = $this->invoice_amount + $this->tax_amount;
        $this->save();
    }

    /**
     * Check if credit note can be submitted to EFRIS.
     */
    public function canBeSubmitted(): bool
    {
        return $this->status === 'DRAFT' && $this->items->count() > 0 && $this->total_amount > 0;
    }

    /**
     * Check if credit note can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['DRAFT', 'SUBMITTED']) && !$this->fdn;
    }

    /**
     * Scope for draft credit notes.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'DRAFT');
    }

    /**
     * Scope for submitted credit notes.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'SUBMITTED');
    }

    /**
     * Scope for approved credit notes.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    /**
     * Scope for cancelled credit notes.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'CANCELLED');
    }
} 