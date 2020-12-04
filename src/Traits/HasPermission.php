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
        return $this->belongsToMany(Permission::class)
                ->wherePivot(
                    'resource_id', 
                    $resource ? $resource->id : $resource
                )
                ->wherePivot(
                    'resource_type', 
                    $resource ? get_class($resource) : $resource
                )
                ->withPivot([
                    'resource_id',
                    'resource_type'
                ]);
    }

    /**
     * @inheritdoc
     */
    public function hasPermission($permission, Model $resource = null): bool
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
    public function attachPermission($permission, Model $resource = null): void
    {
        if (is_array($permission)) {
            $this->attachPermissions($permission, $resource);
        } else {
            $permission = is_a($permission, Permission::class) ?
                $permission :
                resolve(Permission::class)::findOrFail($permission);

            $this->permissions($resource)->attach([
                $permission->id => [
                    'resource_id' => $resource ? $resource->id : $resource,
                    'resource_type' => $resource ? get_class($resource) : $resource
                ]
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function syncPermissions(array $permissions, Model $resource = null): void
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
    public function detachPermissions(array $permissions, Model $resource = null): void
    {
        $this->permissions($resource)->detach($permissions);
    }

    /**
     * @param Permission[]|int[] $permissions
     * @param Model $resource
     * @throws ModelNotFoundException
     * @return void
     */
    private function attachPermissions(array $permissions, Model $resource = null): void
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
    private function mountDataForPermission($permission, Model $resource = null, array $data): array
    {
        $permission = is_a($permission, Permission::class) ?
            $permission :
            resolve(Permission::class)::findOrFail($permission);

        $data[$permission->id] = [
            'resource_id' => $resource ? $resource->id : $resource,
            'resource_type' => $resource ? get_class($resource) : $resource
        ];
        return $data;
    }
}
