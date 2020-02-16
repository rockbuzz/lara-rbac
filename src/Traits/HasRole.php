<?php

declare(strict_types=1);

namespace Rockbuzz\LaraRbac\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Rockbuzz\LaraRbac\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRole
{
    /**
     * @inheritdoc
     */
    public function roles(Model $resource = null): BelongsToMany
    {
        $belongsToMany = $this->belongsToMany(config('rbac.models.role'));

        if ($resource) {
            $belongsToMany->wherePivot('resource_id', $resource->id)
                ->wherePivot('resource_type', get_class($resource));
        }

        return $belongsToMany->withPivot(['resource_id', 'resource_type']);
    }

    /**
     * @inheritdoc
     */
    public function hasRole($role, Model $resource): bool
    {
        $role = is_a($role, Role::class) ? $role->name : $role;

        return $this->roles($resource)
            ->whereIn('name', explode('|', $role))
            ->exists();
    }

    /**
     * @inheritdoc
     */
    public function attachRole($role, Model $resource): void
    {
        if (is_array($role)) {
            $this->attachRoles($role, $resource);
        } else {
            $role = is_a($role, Role::class) ? $role : Role::findOrFail($role);

            $this->roles($resource)->attach([
                $role->id => [
                    'resource_id' => $resource->id,
                    'resource_type' => get_class($resource)
                ]
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function syncRoles(array $roles, Model $resource): void
    {
        $data = [];

        foreach ($roles as $role) {
            $data = $this->mountDataForRole($role, $resource, $data);
        }

        $this->roles($resource)->sync($data);
    }

    /**
     * @inheritdoc
     */
    public function detachRoles(array $roles, Model $resource): void
    {
        $this->roles($resource)->detach($roles);
    }

    /**
     * @param Role[]|int[] $roles
     * @param Model $resource
     * @throws ModelNotFoundException
     * @return void
     */
    private function attachRoles(array $roles, Model $resource): void
    {
        $data = [];

        foreach ($roles as $role) {
            $data = $this->mountDataForRole($role, $resource, $data);
        }

        $this->roles($resource)->attach($data);
    }

    /**
     * @param $role
     * @param Model $resource
     * @param array $data
     * @return array
     */
    private function mountDataForRole($role, Model $resource, array $data): array
    {
        $role = is_a($role, Role::class) ? $role : Role::findOrFail($role);

        $data[$role->id] = [
            'resource_id' => $resource->id,
            'resource_type' => get_class($resource)
        ];
        return $data;
    }
}
