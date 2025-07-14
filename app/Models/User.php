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

    /**
     * Get the roles for the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    /**
     * Get the primary role for the user.
     */
    public function primaryRole()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->wherePivot('is_primary', true)
                    ->withTimestamps();
    }

    /**
     * Get all permissions for the user through their roles.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
                    ->join('user_roles', 'role_permissions.role_id', '=', 'user_roles.role_id')
                    ->where('user_roles.user_id', $this->id)
                    ->select('permissions.*');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if user has all of the given roles.
     */
    public function hasAllRoles(array $roleNames): bool
    {
        $roleCount = $this->roles()->whereIn('name', $roleNames)->count();
        return $roleCount === count($roleNames);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        return $this->permissions()->whereIn('name', $permissionNames)->exists();
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissionNames): bool
    {
        $permissionCount = $this->permissions()->whereIn('name', $permissionNames)->count();
        return $permissionCount === count($permissionNames);
    }

    /**
     * Assign roles to the user.
     */
    public function assignRoles(array $roleIds, bool $isPrimary = false): void
    {
        $pivotData = [];
        foreach ($roleIds as $roleId) {
            $pivotData[$roleId] = ['is_primary' => $isPrimary];
        }
        $this->roles()->attach($pivotData);
    }

    /**
     * Remove roles from the user.
     */
    public function removeRoles(array $roleIds): void
    {
        $this->roles()->detach($roleIds);
    }

    /**
     * Set primary role for the user.
     */
    public function setPrimaryRole(int $roleId): void
    {
        // Remove primary from all other roles
        $this->roles()->updateExistingPivot($this->roles->pluck('id')->toArray(), ['is_primary' => false]);
        
        // Set the new primary role
        $this->roles()->updateExistingPivot($roleId, ['is_primary' => true]);
    }

    /**
     * Get all role names for the user.
     */
    public function getRoleNames(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * Get all permission names for the user.
     */
    public function getPermissionNames(): array
    {
        return $this->permissions()->pluck('name')->toArray();
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super_admin');
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Get user's primary role name.
     */
    public function getPrimaryRoleName(): ?string
    {
        $primaryRole = $this->primaryRole()->first();
        return $primaryRole ? $primaryRole->name : null;
    }

    /**
     * Get user's primary role display name.
     */
    public function getPrimaryRoleDisplayName(): ?string
    {
        $primaryRole = $this->primaryRole()->first();
        return $primaryRole ? $primaryRole->display_name : null;
    }
}
