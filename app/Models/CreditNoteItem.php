<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNoteItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'cni_id';

    protected $fillable = [
        'credit_note_id',
        'original_item_id',
        'item_name',
        'item_code',
        'quantity',
        'unit_price',
        'uom',
        'tax_category',
        'tax_rate',
        'total_amount',
        'tax_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    /**
     * Get the credit note that this item belongs to.
     */
    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class, 'credit_note_id', 'cn_id');
    }

    /**
     * Get the original invoice item that this credit note item is based on.
     */
    public function originalItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'original_item_id', 'item_id');
    }

    /**
     * Calculate totals for this item.
     */
    public function calculateTotals(): void
    {
        $this->total_amount = $this->quantity * $this->unit_price;
        $this->tax_amount = $this->total_amount * ($this->tax_rate / 100);
        $this->save();
    }

    /**
     * Create a credit note item from an invoice item.
     */
    public static function createFromInvoiceItem(CreditNote $creditNote, InvoiceItem $invoiceItem, float $quantity = null): self
    {
        $item = new self([
            'credit_note_id' => $creditNote->cn_id,
            'original_item_id' => $invoiceItem->item_id,
            'item_name' => $invoiceItem->item_name,
            'item_code' => $invoiceItem->item_code,
            'quantity' => $quantity ?? $invoiceItem->quantity,
            'unit_price' => $invoiceItem->unit_price,
            'uom' => $invoiceItem->uom,
            'tax_category' => $invoiceItem->tax_category,
            'tax_rate' => $invoiceItem->tax_rate,
        ]);

        $item->calculateTotals();
        $item->save();

        return $item;
    }
} 