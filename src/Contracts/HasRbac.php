<?php

declare(strict_types=1);

namespace Rockbuzz\LaraRbac\Contracts;

use Rockbuzz\LaraRbac\Models\{Permission, Role};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\{Model, ModelNotFoundException};

interface HasRbac
{
    /**
     * @param Model|null $resource
     * @return BelongsToMany
     */
    public function roles(Model $resource = null): BelongsToMany;

    /**
     * @param string $role Instance role or role name or roles name separated by |.
     * ex.: super|admin
     * @param Model|null $resource
     * @return bool
     */
    public function hasRole($role, Model $resource = null): bool;

    /**
     * @param Role|int $role
     * @param Model|null $resource
     * @throws ModelNotFoundException
     * @return void
     */
    public function attachRole($role, Model $resource = null): void;

    /**
     * @param Role[]|int[] $roles
     * @param Model|null $resource
     * @return void
     */
    public function syncRoles(array $roles, Model $resource = null): void;

    /**
     * @param int[] $roles roles id
     * @param Model|null $resource
     * @return void
     */
    public function detachRoles(array $roles, Model $resource = null): void;

    /**
     * @param Model|null $resource
     * @return BelongsToMany
     */
    public function permissions(Model $resource = null): BelongsToMany;

    /**
     * @param Permission|string $permission Instance permission or permission name or permissions name separated by |.
     * ex.: post.store|post.update
     * @param Model|null $resource
     * @return bool
     */
    public function hasPermission($permission, Model $resource = null): bool;

    /**
     * @param Permission|int $permission
     * @param Model|null $resource
     * @throws ModelNotFoundException
     * @return void
     */
    public function attachPermission($permission, Model $resource = null): void;

    /**
     * @param Permission[]|int[] $permissions
     * @param Model|null $resource
     * @return void
     */
    public function syncPermissions(array $permissions, Model $resource = null): void;

    /**
     * @param int[] $permissions permissions id
     * @param Model|null $resource
     * @return void
     */
    public function detachPermissions(array $permissions, Model $resource = null): void;
}
