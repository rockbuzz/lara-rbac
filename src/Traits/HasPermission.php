<?php

namespace Rockbuzz\LaraRbac\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rockbuzz\LaraRbac\Models\Permission;

trait HasPermission
{
    /**
     * @inheritdoc
     */
    public function permissions($group = null): BelongsToMany
    {
        return $this->belongsToMany(config('rbac.models.permission'))
            ->wherePivot('group', $group)
            ->withPivot('group');
    }

    /**
     * @inheritdoc
     */
    public function attachPermission($permission, $group = null)
    {
        if ($permission instanceof Permission) {
            $this->attachIfNotHasPermission($permission, $group);
        } elseif (is_numeric($permission)) {
            $this->attachIfNotHasPermission(Permission::findOrFail($permission), $group);
        } else {
            $this->attachIfNotHasPermission(
                Permission::whereName($permission)->firstOrFail(),
                $group
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function syncPermissions(array $permissions, $group = null)
    {
        $this->permissions($group)->detach();
        foreach ($permissions as $permission) {
            $this->attachPermission($permission, $group);
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
        if (
            $this->permissions($group)
            ->whereIn('name', explode('|', $permission))
            ->exists()
        ) {
            return true;
        }
        foreach ($this->roles($group)->get() as $role) {
            if ($role->permissions()->whereIn('name', explode('|', $permission))->exists()) {
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

    /**
     * @param $permission
     * @param $group
     */
    private function attachIfNotHasPermission($permission, $group)
    {
        if (!$this->hasPermission($permission, $group)) {
            $this->permissions()->attach([$permission->id => ['group' => $group]]);
        }
    }
}
