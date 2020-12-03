<?php

namespace Rockbuzz\LaraRbac\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name'
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(config('rbac.models.permission'));
    }

    /**
     * @param Permission|string $permission Instance permission or permission name or permissions name separated by |.
     * ex.: post.store|post.update
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        $permission = is_a($permission, Permission::class) ? $permission->name : $permission;

        return $this->permissions()
            ->whereIn('name', explode('|', $permission))
            ->exists();
    }
}
