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
        $builder = $this->belongsToMany(config('rbac.models.role'))
            ->withPivot('group');

        if ($group) {
            $builder->wherePivot('group', $group);
        }

        return $builder;
    }

    /**
     * @inheritdoc
     */
    public function attachRole(Role $role, $group = null)
    {
        if (! $this->hasRole($role, $group)) {
            $this->roles()->attach([$role->id => ['group' => $group]]);
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
        return $this->roles($group)->whereName($role)->exists();
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
}
