<?php

namespace Tests;

use Rockbuzz\LaraRbac\Models\Role;
use Tests\Models\User;

class HasRoleTest extends TestCase
{
    /**
     * @test
     */
    public function aUserHasRoles()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $role = Role::create([
            'name' => 'admin'
        ]);

        Role::create([
            'name' => 'writer'
        ]);

        $user->attachRole($role, $group);

        $this->assertTrue($user->hasRole($role, $group));
        $this->assertTrue($user->hasRole($role->name, $group));
        $this->assertFalse($user->hasRole('writer', $group));

        $this->assertTrue($user->hasAnyRole([$role], $group));
        $this->assertTrue($user->hasAnyRole([$role->name], $group));
        $this->assertFalse($user->hasAnyRole(['writer'], $group));
        $this->assertTrue($user->hasAnyRole([$role, 'writer'], $group));
        $this->assertTrue($user->hasAnyRole([$role->name, 'writer'], $group));
    }

    /**
     * @test
     */
    public function aUserHasRolesWithStringDivider()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $permission = Role::create([
            'name' => 'admin'
        ]);

        Role::create([
            'name' => 'writer'
        ]);

        Role::create([
            'name' => 'reader'
        ]);

        $user->attachRole($permission->id, $group);

        $this->assertTrue($user->hasRole('admin|writer', $group));
        $this->assertFalse($user->hasRole('writer|reader', $group));
    }

    /**
     * @test
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function itShouldReturnAnExceptionBecauseRoleNameDoesNotExist()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $user->attachRole('admin', $group);
    }

    /**
     * @test
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function itShouldReturnAnExceptionBecauseRoleIdDoesNotExist()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $user->attachRole(0, $group);
    }

    /**
     * @test
     */
    public function itShouldOnlyReturnARoleOfUser()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $role = Role::create([
            'name' => 'admin'
        ]);

        $user->attachRole($role, $group);
        $user->attachRole($role, $group);

        $this->assertEquals(1, $user->roles($group)->count());
    }

    /**
     * @test
     */
    public function aUserCanDetachRole()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $role = Role::create([
            'name' => 'admin'
        ]);

        $user->attachRole($role, $group);
        $user->detachRole($role, $group);

        $this->assertFalse($user->hasRole($role, $group));
    }

    /**
     * @test
     */
    public function aUserCanDetachRoleName()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';
        $groupB = 'Company B';

        $role = Role::create([
            'name' => 'post.create'
        ]);
        $roleDelete = Role::create([
            'name' => 'post.delete'
        ]);

        $user->attachRole($role, $group);
        $user->attachRole($role, $groupB);
        $user->attachRole($roleDelete, $group);

        $user->detachRole($role->name, $group);

        $this->assertFalse($user->hasRole($role, $group));
        $this->assertTrue($user->hasRole($role, $groupB));
        $this->assertTrue($user->hasRole($roleDelete, $group));
    }

    /**
     * @test
     */
    public function aUserHasRolesWithNullableGroup()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);

        $group = 'Company A';

        $role = Role::create([
            'name' => 'admin'
        ]);

        $user->attachRole($role);

        $this->assertFalse($user->hasRole($role, $group));
        $this->assertTrue($user->hasRole($role));
    }
}
