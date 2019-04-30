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
        } else {
            $this->attachIfNotHasPermission(Permission::findOrFail($permission), $group);
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
    public function hasPermission(string $permission, $group = null): bool
    {
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
    public function detachPermission(array $permissions, $group = null)
    {
        $this->permissions($group)->detach($permissions);
    }

    /**
     * @param Permission $permission
     * @param $group
     */
    private function attachIfNotHasPermission(Permission $permission, $group)
    {
        if (!$this->hasPermission($permission->name, $group)) {
            $this->permissions()->attach([$permission->id => ['group' => $group]]);
        }
    }
}
