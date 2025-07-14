<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'sun_reference',
        'item_code',
        'quantity',
        'reference',
        'status',
        'remarks',
        'qrcode_path',
        'barcode_path',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::updating(function ($stock) {
            if ($stock->isDirty('item_code')) {
                if ($stock->qrcode_path && Storage::disk('public')->exists($stock->qrcode_path)) {
                    Storage::disk('public')->delete($stock->qrcode_path);
                }
                if ($stock->barcode_path && Storage::disk('public')->exists($stock->barcode_path)) {
                    Storage::disk('public')->delete($stock->barcode_path);
                }
                $stock->qrcode_path = null;
                $stock->barcode_path = null;
            }
        });
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function good()
    {
        return $this->belongsTo(EfrisGood::class, 'item_code', 'eg_code');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Methods
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'approved' => '<span class="badge badge-success">Approved</span>',
            'rejected' => '<span class="badge badge-danger">Rejected</span>',
            default => '<span class="badge badge-secondary">Unknown</span>'
        };
    }

    public function getFormattedQuantityAttribute()
    {
        return number_format($this->quantity, 2);
    }
}
