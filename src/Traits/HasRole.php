<?php

namespace Rockbuzz\LaraRbac\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rockbuzz\LaraRbac\Models\Role;

trait HasRole
{
    /**
     * @inheritdoc
     */
    public function roles($group = null): BelongsToMany
    {
        return $this->belongsToMany(config('rbac.models.role'))
            ->wherePivot('group', $group)
            ->withPivot('group');
    }

    /**
     * @inheritdoc
     */
    public function attachRole($role, $group = null)
    {
        if ($role instanceof Role) {
            $this->attachIfNotHasRole($role, $group);
        } elseif (is_numeric($role)) {
            $this->attachIfNotHasRole(Role::findOrFail($role), $group);
        } else {
            $this->attachIfNotHasRole(
                Role::whereName($role)->firstOrFail(),
                $group
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function hasRole(string $role, $group = null): bool
    {
        return $this->roles($group)
            ->whereIn('name', explode('|', $role))
            ->exists();
    }

    /**
     * @inheritdoc
     */
    public function detachRole(array $roles, $group = null)
    {
        $this->roles($group)->detach($roles);
    }

    /**
     * @param Role $role
     * @param mixed $group
     */
    private function attachIfNotHasRole(Role $role, $group)
    {
        if (!$this->hasRole($role->name, $group)) {
            $this->roles()->attach([$role->id => ['group' => $group]]);
        }
    }
}
