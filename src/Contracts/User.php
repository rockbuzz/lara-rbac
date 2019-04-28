<?php

namespace Rockbuzz\LaraRbac\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rockbuzz\LaraRbac\Models\Permission;
use Rockbuzz\LaraRbac\Models\Role;

interface User
{
    /**
     * @param mixed|null $group
     * @return BelongsToMany
     */
    public function roles($group = null): BelongsToMany;

    /**
     * @param Role $role
     * @param mixed|null $group
     * @return void
     */
    public function attachRole(Role $role, $group = null);

    /**
     * @param Role|string $role
     * @param mixed|null $group
     * @return bool
     */
    public function hasRole($role, $group = null): bool;

    /**
     * @param Role[]|string[] $roles
     * @param mixed|null $group
     * @return bool
     */
    public function hasAnyRole(array $roles, $group = null): bool;

    /**
     * @param Role|string $role
     * @param mixed|null $group
     * @return void
     */
    public function detachRole($role, $group = null);

    /**
     * @param mixed|null $group
     * @return BelongsToMany
     */
    public function permissions($group = null): BelongsToMany;

    /**
     * @param Permission $permission
     * @param mixed|null $group
     * @return void
     */
    public function attachPermission(Permission $permission, $group = null);

    /**
     * @param Permission[]|string[] $permissions
     * @param mixed|null $group
     * @return void
     */
    public function syncPermissions(array $permissions, $group = null);

    /**
     * @param Permission|string $permission
     * @param mixed|null $group
     * @return bool
     */
    public function hasPermission($permission, $group = null): bool;

    /**
     * @param Permission[]|string[] $permissions
     * @param mixed|null $group
     * @return bool
     */
    public function hasAnyPermission(array $permissions, $group = null): bool;

    /**
     * @param Permission|string $permission
     * @param mixed|null $group
     * @return void
     */
    public function detachPermission($permission, $group = null);
}
