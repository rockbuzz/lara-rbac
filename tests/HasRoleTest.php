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

        $group = 'group-name';

        $roleSuper = Role::create([
            'name' => 'super'
        ]);

        $roleAdmin = Role::create([
            'name' => 'admin'
        ]);

        $roleWriter = Role::create([
            'name' => 'writer'
        ]);

        $roleReader = Role::create([
            'name' => 'reader'
        ]);

        $user->attachRole($roleAdmin, $group);
        $user->attachRole($roleWriter->id, $group);
        $user->attachRole($roleReader, $group);

        $this->assertTrue($user->hasRole($roleAdmin->name, $group));
        $this->assertTrue($user->hasRole($roleWriter->name, $group));
        $this->assertTrue($user->hasRole($roleReader->name, $group));
        $this->assertFalse($user->hasRole($roleSuper->name, $group));
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

        $group = 'group-name';

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

        $group = 'group-name';

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

        $group = 'group-name';

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

        $group = 'group-name';

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

        $group = 'group-name';

        $role = Role::create([
            'name' => 'admin'
        ]);
        $roleWriter = Role::create([
            'name' => 'writer'
        ]);
        $roleReader = Role::create([
            'name' => 'reader'
        ]);
        $roleSuper = Role::create([
            'name' => 'super'
        ]);

        $user->attachRole($role->id, $group);
        $user->attachRole($roleWriter->id, $group);
        $user->attachRole($roleReader->id, $group);
        $user->attachRole($roleSuper->id, 'group-name-other');

        $user->detachRole([$role->id, $roleWriter->id, $roleSuper->id], $group);

        $this->assertFalse($user->hasRole($role->name, $group));
        $this->assertFalse($user->hasRole($roleWriter->name, $group));
        $this->assertTrue($user->hasRole($roleReader->name, $group));
        $this->assertFalse($user->hasRole($roleSuper->name, $group));
        $this->assertTrue($user->hasRole($roleSuper->name, 'group-name-other'));
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

        $group = 'group-name';

        $role = Role::create([
            'name' => 'admin'
        ]);

        $roleWriter = Role::create([
            'name' => 'writer'
        ]);

        $user->attachRole($role);
        $user->attachRole($roleWriter, $group);

        $this->assertFalse($user->hasRole($role->name, $group));
        $this->assertFalse($user->hasRole($roleWriter->name));
        $this->assertTrue($user->hasRole($role->name));
    }
}
