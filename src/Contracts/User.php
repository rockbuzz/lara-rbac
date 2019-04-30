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
    public function roles($group = Group::DEFAULT): BelongsToMany;

    /**
     * @param Role|int $role
     * @param mixed|null $group
     * @throws ModelNotFoundException
     * @return void
     */
    public function attachRole($role, $group = Group::DEFAULT);

    /**
     * @param string $role
     * @param mixed|null $group
     * @return bool
     */
    public function hasRole(string $role, $group = Group::DEFAULT): bool;

    /**
     * @param int[] $roles
     * @param mixed|null $group
     * @return void
     */
    public function detachRole(array $roles, $group = Group::DEFAULT);

    /**
     * @param mixed|null $group
     * @return BelongsToMany
     */
    public function permissions($group = Group::DEFAULT): BelongsToMany;

    /**
     * @param Permission|int $permission
     * @param mixed|null $group
     * @throws ModelNotFoundException
     * @return void
     */
    public function attachPermission($permission, $group = Group::DEFAULT);

    /**
     * @param Permission[]|string[] $permissions
     * @param mixed|null $group
     * @return void
     */
    public function syncPermissions(array $permissions, $group = Group::DEFAULT);

    /**
     * @param string $permission
     * @param mixed|null $group
     * @return bool
     */
    public function hasPermission(string $permission, $group = Group::DEFAULT): bool;

    /**
     * @param int[] $permissions
     * @param mixed|null $group
     * @return void
     */
    public function detachPermission(array $permissions, $group = Group::DEFAULT);
}
