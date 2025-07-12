<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_name',
        'user_surname',
        'user_othername',
        'user_phone',
        'user_department_id',
        'user_designation',
        'user_active',
        'user_online',
        'user_forgot_password',
        'user_last_logged_in',
        'user_last_active',
        'user_last_changed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'user_active' => 'boolean',
            'user_online' => 'boolean',
            'user_forgot_password' => 'boolean',
            'user_last_logged_in' => 'datetime',
            'user_last_active' => 'datetime',
            'user_last_changed' => 'datetime',
        ];
    }

    /**
     * Get the department that owns the user.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'user_department_id', 'dept_id');
    }

    /**
     * Get the designation that owns the user.
     */
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'user_designation', 'designation_id');
    }

    /**
     * Get the access rights for the user.
     */
    public function accessRights()
    {
        return $this->hasMany(AccessRight::class);
    }

    /**
     * Get the invoices created by the user.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    /**
     * Get the audit trails for the user.
     */
    public function auditTrails()
    {
        return $this->hasMany(AuditTrail::class);
    }

    /**
     * Check if user has access to a specific page and right.
     */
    public function hasAccess($page, $right)
    {
        return $this->accessRights()
            ->where('page_name', $page)
            ->where('right_type', $right)
            ->where('active', true)
            ->exists();
    }

    /**
     * Get full name of the user.
     */
    public function getFullNameAttribute()
    {
        return trim($this->user_surname . ' ' . $this->user_othername);
    }

    /**
     * Update user's last activity.
     */
    public function updateLastActivity()
    {
        $this->update([
            'user_last_active' => now(),
            'user_online' => true
        ]);
    }

    /**
     * Mark user as offline.
     */
    public function markOffline()
    {
        $this->update([
            'user_online' => false,
            'user_last_logged_in' => now()
        ]);
    }
}
