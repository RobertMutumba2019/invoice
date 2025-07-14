<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'business_name',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'tin_number',
        'vrn_number',
        'address',
        'city',
        'country',
        'postal_code',
        'customer_type',
        'customer_category',
        'credit_limit',
        'current_balance',
        'payment_terms',
        'bank_name',
        'bank_account',
        'bank_branch',
        'notes',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'payment_terms' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class, 'customer_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('customer_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('customer_category', $category);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('business_name', 'like', "%{$search}%")
              ->orWhere('contact_person', 'like', "%{$search}%")
              ->orWhere('customer_code', 'like', "%{$search}%")
              ->orWhere('tin_number', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Methods
    public function getCustomerTypeNameAttribute()
    {
        return match($this->customer_type) {
            'INDIVIDUAL' => 'Individual',
            'COMPANY' => 'Company',
            'GOVERNMENT' => 'Government',
            'NGO' => 'NGO',
            default => $this->customer_type,
        };
    }

    public function getCustomerCategoryNameAttribute()
    {
        return match($this->customer_category) {
            'REGULAR' => 'Regular',
            'WHOLESALE' => 'Wholesale',
            'RETAIL' => 'Retail',
            'EXPORT' => 'Export',
            'VIP' => 'VIP',
            default => $this->customer_category,
        };
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active 
            ? '<span class="badge badge-success">Active</span>'
            : '<span class="badge badge-danger">Inactive</span>';
    }

    public function getFormattedCreditLimitAttribute()
    {
        return number_format($this->credit_limit, 2);
    }

    public function getFormattedCurrentBalanceAttribute()
    {
        return number_format($this->current_balance, 2);
    }

    public function getAvailableCreditAttribute()
    {
        return $this->credit_limit - $this->current_balance;
    }

    public function getFormattedAvailableCreditAttribute()
    {
        return number_format($this->available_credit, 2);
    }

    public function getCreditStatusAttribute()
    {
        if ($this->current_balance >= $this->credit_limit) {
            return 'OVER_LIMIT';
        } elseif ($this->current_balance >= ($this->credit_limit * 0.8)) {
            return 'NEAR_LIMIT';
        } else {
            return 'OK';
        }
    }

    public function getCreditStatusBadgeAttribute()
    {
        return match($this->credit_status) {
            'OVER_LIMIT' => '<span class="badge badge-danger">Over Limit</span>',
            'NEAR_LIMIT' => '<span class="badge badge-warning">Near Limit</span>',
            'OK' => '<span class="badge badge-success">OK</span>',
            default => '<span class="badge badge-secondary">Unknown</span>'
        };
    }

    // Static methods
    public static function generateCustomerCode()
    {
        $lastCustomer = self::orderBy('id', 'desc')->first();
        $lastNumber = $lastCustomer ? intval(substr($lastCustomer->customer_code, 3)) : 0;
        $newNumber = $lastNumber + 1;
        return 'CUS' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
