<?php

namespace Tests;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rockbuzz\LaraRbac\Models\Permission;
use Rockbuzz\LaraRbac\Models\Role;

class RoleTest extends TestCase
{
    /**
     * @test
     */
    public function aRoleHasPermissions()
    {
        $role = Role::create([
            'name' => 'admin'
        ]);

        $permission = Permission::create([
            'name' => 'post.create'
        ]);

        $permissionDelete = Permission::create([
            'name' => 'post.delete'
        ]);

        $role->permissions()->attach($permission);

        $this->assertInstanceOf(BelongsToMany::class, $role->permissions());
        $this->assertEquals(1, $role->permissions->count());
        $this->assertTrue($role->hasPermission($permission));
        $this->assertTrue($role->hasPermission($permission->name));
        $this->assertTrue($role->hasAnyPermission([$permission, $permissionDelete]));
        $this->assertTrue($role->hasAnyPermission([$permission->name, $permissionDelete->name]));
        $this->assertFalse($role->hasAnyPermission([$permissionDelete]));
        $this->assertFalse($role->hasAnyPermission([$permissionDelete->name]));

        $role->permissions()->detach($permission);

        $this->assertFalse($role->hasPermission($permission));
    }
}
