<?php

namespace Tests;

use Rockbuzz\LaraRbac\Models\Permission;
use Rockbuzz\LaraRbac\Models\Role;
use Tests\Models\User;

class HasPermissionTest extends TestCase
{
    /**
     * @test
     */
    public function aUserHasPermissions()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $permission = Permission::create([
            'name' => 'post.create'
        ]);

        $permissionD = Permission::create([
            'name' => 'post.delete'
        ]);

        $user->attachPermission($permission, $group);

        $this->assertTrue($user->hasPermission($permission, $group));
        $this->assertTrue($user->hasPermission($permission->name, $group));
        $this->assertFalse($user->hasPermission($permissionD->name, $group));

        $this->assertTrue($user->hasAnyPermission([$permission], $group));
        $this->assertTrue($user->hasAnyPermission([$permission->name], $group));
        $this->assertFalse($user->hasAnyPermission([$permissionD->name], $group));
        $this->assertTrue($user->hasAnyPermission([$permission, $permissionD->name], $group));
        $this->assertTrue($user->hasAnyPermission([$permission->name, $permissionD->name], $group));

        $user->syncPermissions([$permissionD], $group);

        $this->assertFalse($user->hasPermission($permission->name, $group));
        $this->assertTrue($user->hasPermission($permissionD->name, $group));
    }

    /**
     * @test
     */
    public function aUserHasPermissionsWithStringDivider()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $permission = Permission::create([
            'name' => 'post.create'
        ]);

        Permission::create([
            'name' => 'post.edit'
        ]);

        Permission::create([
            'name' => 'post.delete'
        ]);

        $user->attachPermission($permission->id, $group);

        $this->assertTrue($user->hasPermission('post.create|post.delete', $group));
        $this->assertFalse($user->hasPermission('post.edit|post.delete', $group));
    }

    /**
     * @test
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function itShouldReturnAnExceptionBecausePermissionNameDoesNotExist()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $user->attachPermission('create.post', $group);
    }

    /**
     * @test
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function itShouldReturnAnExceptionBecausePermissionIdDoesNotExist()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $user->attachPermission(0, $group);
    }

    /**
     * @test
     */
    public function itShouldOnlyReturnAPermissionOfUser()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $permission = Permission::create([
            'name' => 'post.create'
        ]);

        $user->attachPermission($permission, $group);
        $user->attachPermission($permission, $group);

        $this->assertEquals(1, $user->permissions($group)->count());
    }

    /**
     * @test
     */
    public function aUserCanRevokePermission()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $permission = Permission::create([
            'name' => 'post.create'
        ]);

        $user->attachPermission($permission, $group);
        $user->detachPermission($permission, $group);

        $this->assertFalse($user->hasPermission($permission, $group));
    }

    /**
     * @test
     */
    public function aUserCanRevokePermissionName()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';
        $groupB = 'Company B';

        $permission = Permission::create([
            'name' => 'post.create'
        ]);
        $permissionDelete = Permission::create([
            'name' => 'post.delete'
        ]);

        $user->attachPermission($permission, $group);
        $user->attachPermission($permission, $groupB);
        $user->attachPermission($permissionDelete, $group);

        $user->detachPermission($permission->name, $group);

        $this->assertFalse($user->hasPermission($permission, $group));
        $this->assertTrue($user->hasPermission($permission, $groupB));
        $this->assertTrue($user->hasPermission($permissionDelete, $group));
    }

    /**
     * @test
     */
    public function aUserHasPermissionsOfRoles()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';
        $groupB = 'Company B';

        $permission = Permission::create([
            'name' => 'post.create'
        ]);

        $permissionD = Permission::create([
            'name' => 'post.delete'
        ]);

        $role = Role::create([
            'name' => 'writer'
        ]);

        $role->permissions()->attach($permission);

        $user->attachRole($role, $group);

        $this->assertTrue($user->hasPermission($permission, $group));
        $this->assertTrue($user->hasPermission($permission->name, $group));
        $this->assertFalse($user->hasPermission($permissionD->name, $group));
        $this->assertFalse($user->hasPermission($permission->name, $groupB));

        $this->assertTrue($user->hasAnyPermission([$permission], $group));
        $this->assertTrue($user->hasAnyPermission([$permission->name], $group));
        $this->assertFalse($user->hasAnyPermission([$permissionD->name], $group));
        $this->assertTrue($user->hasAnyPermission([$permission, $permissionD->name], $group));
        $this->assertTrue($user->hasAnyPermission([$permission->name, $permissionD->name], $group));
    }

    /**
     * @test
     */
    public function aUserHasPermissionsWithNullableGroup()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $role = Permission::create([
            'name' => 'post.create'
        ]);

        $user->attachPermission($role);

        $this->assertFalse($user->hasPermission($role, $group));
        $this->assertTrue($user->hasPermission($role));
    }
}
