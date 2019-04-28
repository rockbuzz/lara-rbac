<?php

namespace Rockbuzz\LaraRbac\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $guarded = [];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * @param Permission|string $permission
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        if ($permission instanceof Permission) {
            return $this->permissions()->whereName($permission->name)->exists();
        }
        return $this->permissions()->whereName($permission)->exists();
    }

    /**
     * @param Permission[]|string[] $permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
}
