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

        $group = 'group-name';

        $permissionReadPost = Permission::create([
            'name' => 'post.read'
        ]);

        $permissionCreatePost = Permission::create([
            'name' => 'create.post'
        ]);

        $permissionUpdatePost = Permission::create([
            'name' => 'update.post'
        ]);

        $permissionDeletePost = Permission::create([
            'name' => 'delete.post'
        ]);

        $user->attachPermission($permissionReadPost, $group);
        $user->attachPermission($permissionCreatePost->id, $group);

        $this->assertTrue($user->hasPermission($permissionReadPost->name, $group));
        $this->assertTrue($user->hasPermission($permissionCreatePost->name, $group));
        $this->assertFalse($user->hasPermission($permissionUpdatePost, $group));
        $this->assertFalse($user->hasPermission($permissionDeletePost->name, $group));

        $user->syncPermissions([$permissionUpdatePost, $permissionDeletePost], $group);

        $this->assertFalse($user->hasPermission($permissionReadPost->name, $group));
        $this->assertTrue($user->hasPermission($permissionUpdatePost->name, $group));
        $this->assertTrue($user->hasPermission($permissionDeletePost->name, $group));
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

        $group = 'group-name';

        $permission = Permission::create([
            'name' => 'create.post'
        ]);

        Permission::create([
            'name' => 'post.edit'
        ]);

        Permission::create([
            'name' => 'post.delete'
        ]);

        $user->attachPermission($permission->id, $group);

        $this->assertTrue($user->hasPermission('create.post|post.delete', $group));
        $this->assertFalse($user->hasPermission('post.edit|post.delete', $group));
    }

    /**
     * @test
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function itShouldReturnAnExceptionBecausePermissionName()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'group-name';

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

        $group = 'group-name';

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

        $group = 'group-name';

        $permission = Permission::create([
            'name' => 'create.post'
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

        $group = 'group-name';

        $permission = Permission::create([
            'name' => 'create.post'
        ]);
        $permissionUp = Permission::create([
            'name' => 'update.post'
        ]);
        $permissionDel = Permission::create([
            'name' => 'delete.post'
        ]);

        $user->attachPermission($permission->id, $group);
        $user->attachPermission($permissionUp->id, $group);
        $user->attachPermission($permissionDel->id, 'group-name-other');

        $user->detachPermission([$permission->id, $permissionUp->id, $permissionDel->id], $group);

        $this->assertFalse($user->hasPermission($permission->name, $group));
        $this->assertFalse($user->hasPermission($permissionUp->name, $group));
        $this->assertFalse($user->hasPermission($permissionDel->name, $group));
        $this->assertTrue($user->hasPermission($permissionDel->name, 'group-name-other'));
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

        $group = 'group-name';
        $groupB = 'Company B';

        $permission = Permission::create([
            'name' => 'create.post'
        ]);

        $permissionD = Permission::create([
            'name' => 'post.delete'
        ]);

        $role = Role::create([
            'name' => 'writer'
        ]);

        $role->permissions()->attach($permission);

        $user->attachRole($role, $group);

        $this->assertTrue($user->hasPermission($permission->name, $group));
        $this->assertFalse($user->hasPermission($permissionD->name, $group));
        $this->assertFalse($user->hasPermission($permission->name, $groupB));
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

        $group = 'group-name';

        $permission = Permission::create([
            'name' => 'create.post'
        ]);

        $permissionUp = Permission::create([
            'name' => 'update.post'
        ]);

        $user->attachPermission($permission);
        $user->attachPermission($permissionUp, $group);

        $this->assertFalse($user->hasPermission($permission->name, $group));
        $this->assertFalse($user->hasPermission($permissionUp->name));
        $this->assertTrue($user->hasPermission($permission->name));
    }
}
