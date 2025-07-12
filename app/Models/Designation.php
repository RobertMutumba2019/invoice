<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $primaryKey = 'designation_id';

    protected $fillable = [
        'designation_name',
        'designation_description',
        'designation_active',
        'designation_added_by',
        'designation_date_added',
    ];

    protected $casts = [
        'designation_active' => 'boolean',
        'designation_date_added' => 'datetime',
    ];

    /**
     * Get the users with this designation.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'user_designation', 'designation_id');
    }

    /**
     * Get the user who added this designation.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'designation_added_by');
    }

    /**
     * Scope to get only active designations.
     */
    public function scopeActive($query)
    {
        return $query->where('designation_active', true);
    }
} 