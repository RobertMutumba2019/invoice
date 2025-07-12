<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessRight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'page_name',
        'right_type',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the user that owns the access right.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only active access rights.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter by page name.
     */
    public function scopeForPage($query, $pageName)
    {
        return $query->where('page_name', $pageName);
    }

    /**
     * Scope to filter by right type.
     */
    public function scopeForRight($query, $rightType)
    {
        return $query->where('right_type', $rightType);
    }
} 