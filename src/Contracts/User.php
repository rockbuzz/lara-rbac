<?php

namespace Rockbuzz\LaraRbac\Contracts;

use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * @param Role|int $role
     * @param mixed|null $group
     * @throws ModelNotFoundException
     * @return void
     */
    public function attachRole($role, $group = null);

    /**
     * @param string $role
     * @param mixed|null $group
     * @return bool
     */
    public function hasRole(string $role, $group = null): bool;

    /**
     * @param int[] $roles
     * @param mixed|null $group
     * @return void
     */
    public function detachRole(array $roles, $group = null);

    /**
     * @param mixed|null $group
     * @return BelongsToMany
     */
    public function permissions($group = null): BelongsToMany;

    /**
     * @param Permission|int $permission
     * @param mixed|null $group
     * @throws ModelNotFoundException
     * @return void
     */
    public function attachPermission($permission, $group = null);

    /**
     * @param Permission[]|string[] $permissions
     * @param mixed|null $group
     * @return void
     */
    public function syncPermissions(array $permissions, $group = null);

    /**
     * @param string $permission
     * @param mixed|null $group
     * @return bool
     */
    public function hasPermission(string $permission, $group = null): bool;

    /**
     * @param int[] $permissions
     * @param mixed|null $group
     * @return void
     */
    public function detachPermission(array $permissions, $group = null);
}
