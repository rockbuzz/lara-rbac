<?php

namespace Rockbuzz\LaraRbac\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rockbuzz\LaraRbac\Models\Permission;

trait HasPermission
{
    /**
     * @inheritdoc
     */
    public function permissions($group = null): BelongsToMany
    {
        $builder = $this->belongsToMany(config('rbac.models.permission'))
            ->withPivot('group');

        if ($group) {
            $builder->wherePivot('group', $group);
        }

        return $builder;
    }

    /**
     * @inheritdoc
     */
    public function attachPermission(Permission $permission, $group = null)
    {
        if (! $this->hasPermission($permission, $group)) {
            $this->permissions()->attach([$permission->id => ['group' => $group]]);
        }
    }

    /**
     * @inheritdoc
     */
    public function syncPermissions(array $permissions, $group = null)
    {
        foreach ($permissions as $permission) {
            if ($permission instanceof Permission) {
                $this->permissions()->sync([$permission->id => ['group' => $group]]);
            } else {
                $this->permissions()->sync([$permission => ['group' => $group]]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function hasPermission($permission, $group = null): bool
    {
        if ($permission instanceof Permission) {
            if ($this->permissions($group)->whereName($permission->name)->exists()) {
                return true;
            }
            foreach ($this->roles($group)->get() as $role) {
                if ($role->permissions()->whereName($permission->name)->exists()) {
                    return true;
                }
            }
            return false;
        }
        if ($this->permissions($group)->whereName($permission)->exists()) {
            return true;
        }
        foreach ($this->roles($group)->get() as $role) {
            if ($role->permissions()->whereName($permission)->exists()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function hasAnyPermission(array $permissions, $group = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission, $group)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function detachPermission($permission, $group = null)
    {
        if ($this->hasPermission($permission, $group)) {
            if ($permission instanceof Permission) {
                $this->permissions($group)->detach($permission->id);
            } else {
                $row = Permission::whereName($permission)->first();
                $this->permissions($group)->detach($row->id);
            }
        }
    }
}
