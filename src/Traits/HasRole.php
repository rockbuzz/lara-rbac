<?php

namespace Rockbuzz\LaraRbac\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rockbuzz\LaraRbac\Contracts\Group;
use Rockbuzz\LaraRbac\Models\Role;

trait HasRole
{
    /**
     * @inheritdoc
     */
    public function roles($group = Group::DEFAULT): BelongsToMany
    {
        return $this->belongsToMany(config('rbac.models.role'))
            ->wherePivot('group', $group)
            ->withPivot('group');
    }

    /**
     * @inheritdoc
     */
    public function attachRole($role, $group = Group::DEFAULT)
    {
        if ($role instanceof Role) {
            $this->attachIfNotHasRole($role, $group);
        } else {
            $this->attachIfNotHasRole(Role::findOrFail($role), $group);
        }
    }

    /**
     * @inheritdoc
     */
    public function hasRole(string $role, $group = Group::DEFAULT): bool
    {
        return $this->roles($group)
            ->whereIn('name', explode('|', $role))
            ->exists();
    }

    /**
     * @inheritdoc
     */
    public function detachRole(array $roles, $group = Group::DEFAULT)
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
