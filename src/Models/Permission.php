<?php

namespace Rockbuzz\LaraRbac\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name'
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @param Role|string $role Instance role or role name or roles name separated by |.
     * ex.: super|admin
     * @return bool
     */
    public function hasRole($role): bool
    {
        $role = is_a($role, Role::class) ? $role->name : $role;

        return $this->roles()
            ->whereIn('name', explode('|', $role))
            ->exists();
    }
}
