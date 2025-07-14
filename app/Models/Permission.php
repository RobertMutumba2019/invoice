<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'module',
        'action',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
                    ->withTimestamps();
    }

    /**
     * Get the users that have this permission through their roles.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_permissions', 'permission_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Scope to get only active permissions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only system permissions.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to get only custom permissions.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope to get permissions by module.
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope to get permissions by action.
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get permission by name.
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Check if permission exists by name.
     */
    public static function existsByName(string $name): bool
    {
        return static::where('name', $name)->exists();
    }

    /**
     * Get all modules.
     */
    public static function getModules(): array
    {
        return static::distinct()->pluck('module')->filter()->toArray();
    }

    /**
     * Get all actions.
     */
    public static function getActions(): array
    {
        return static::distinct()->pluck('action')->filter()->toArray();
    }

    /**
     * Get permissions grouped by module.
     */
    public static function getGroupedByModule(): array
    {
        return static::active()
            ->orderBy('module')
            ->orderBy('display_name')
            ->get()
            ->groupBy('module')
            ->toArray();
    }

    /**
     * Get permissions grouped by action.
     */
    public static function getGroupedByAction(): array
    {
        return static::active()
            ->orderBy('action')
            ->orderBy('display_name')
            ->get()
            ->groupBy('action')
            ->toArray();
    }

    /**
     * Create a new permission.
     */
    public static function createPermission(string $name, string $displayName, string $module = null, string $action = null, string $description = null): self
    {
        return static::create([
            'name' => $name,
            'display_name' => $displayName,
            'description' => $description,
            'module' => $module,
            'action' => $action,
            'is_system' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Get full permission name (module.action).
     */
    public function getFullName(): string
    {
        if ($this->module && $this->action) {
            return $this->module . '.' . $this->action;
        }
        return $this->name;
    }
} 