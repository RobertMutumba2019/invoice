<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $primaryKey = 'dept_id';

    protected $fillable = [
        'dept_name',
        'dept_description',
        'dept_active',
        'dept_added_by',
        'dept_date_added',
    ];

    protected $casts = [
        'dept_active' => 'boolean',
        'dept_date_added' => 'datetime',
    ];

    /**
     * Get the users that belong to this department.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'user_department_id', 'dept_id');
    }

    /**
     * Get the user who added this department.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'dept_added_by');
    }

    /**
     * Scope to get only active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('dept_active', true);
    }
} 