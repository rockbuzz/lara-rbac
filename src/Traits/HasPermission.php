<?php

declare(strict_types=1);

namespace Rockbuzz\LaraRbac\Traits;

use Rockbuzz\LaraRbac\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\{Model, ModelNotFoundException};

trait HasPermission
{
    /**
     * @inheritdoc
     */
    public function permissions(Model $resource = null): BelongsToMany
    {
        $belongsToMany = $this->belongsToMany(config('rbac.models.permission'));

        if ($resource) {
            $belongsToMany->wherePivot(config('rbac.tables.morph_columns.id', 'resource_id'), $resource->id)
                ->wherePivot(config('rbac.tables.morph_columns.type', 'resource_type'), get_class($resource));
        }

        return $belongsToMany->withPivot([
            config('rbac.tables.morph_columns.id', 'resource_id'),
            config('rbac.tables.morph_columns.type', 'resource_type')
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hasPermission($permission, Model $resource): bool
    {
        $permission = is_a($permission, Permission::class) ? $permission->name : $permission;

        $arrayPermissionsName = explode('|', $permission);

        if ($this->permissions($resource)
            ->whereIn('name', $arrayPermissionsName)
            ->exists()) {
            return true;
        }

        foreach ($this->roles($resource)->get() as $role) {
            if (array_intersect($role->permissions()->pluck('name')->toArray(), $arrayPermissionsName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function attachPermission($permission, Model $resource): void
    {
        if (is_array($permission)) {
            $this->attachPermissions($permission, $resource);
        } else {
            $permission = is_a($permission, Permission::class) ?
                $permission :
                resolve(config('rbac.models.permission'))::findOrFail($permission);

            $this->permissions($resource)->attach([
                $permission->id => [
                    config('rbac.tables.morph_columns.id', 'resource_id') => $resource->id,
                    config('rbac.tables.morph_columns.type', 'resource_type') => get_class($resource)
                ]
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function syncPermissions(array $permissions, Model $resource): void
    {
        $data = [];

        foreach ($permissions as $permission) {
            $data = $this->mountDataForPermission($permission, $resource, $data);
        }

        $this->permissions($resource)->sync($data);
    }

    /**
     * @inheritdoc
     */
    public function detachPermissions(array $permissions, Model $resource): void
    {
        $this->permissions($resource)->detach($permissions);
    }

    /**
     * @param Permission[]|int[] $permissions
     * @param Model $resource
     * @throws ModelNotFoundException
     * @return void
     */
    private function attachPermissions(array $permissions, Model $resource): void
    {
        $data = [];

        foreach ($permissions as $permission) {
            $data = $this->mountDataForPermission($permission, $resource, $data);
        }

        $this->permissions($resource)->attach($data);
    }

    /**
     * @param $permission
     * @param Model $resource
     * @param array $data
     * @return array
     */
    private function mountDataForPermission($permission, Model $resource, array $data): array
    {
        $permission = is_a($permission, Permission::class) ?
            $permission :
            resolve(config('rbac.models.permission'))::findOrFail($permission);

        $data[$permission->id] = [
            config('rbac.tables.morph_columns.id', 'resource_id') => $resource->id,
            config('rbac.tables.morph_columns.type', 'resource_type') => get_class($resource)
        ];
        return $data;
    }
}
