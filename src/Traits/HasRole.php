<?php

namespace Rockbuzz\LaraRbac\Traits;

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
    public function hasRole($role, $group = null): bool
    {
        if ($role instanceof Role) {
            return $this->roles($group)->whereName($role->name)->exists();
        }

        return $this->roles($group)
            ->whereIn('name', explode('|', $role))
            ->exists();
    }

    /**
     * @inheritdoc
     */
    public function hasAnyRole(array $roles, $group = null): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role, $group)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function detachRole($role, $group = null)
    {
        if ($this->hasRole($role, $group)) {
            if ($role instanceof Role) {
                $this->roles($group)->detach($role->id);
            } else {
                $row = Role::whereName($role)->first();
                $this->roles($group)->detach($row->id);
            }
        }
    }

    /**
     * @param mixed $role
     * @param mixed $group
     */
    private function attachIfNotHasRole($role, $group)
    {
        if (!$this->hasRole($role, $group)) {
            $this->roles()->attach([$role->id => ['group' => $group]]);
        }
    }
}
