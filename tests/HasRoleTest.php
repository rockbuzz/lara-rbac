<?php

namespace Tests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\Models\Workspace;
use Rockbuzz\LaraRbac\Models\Role;

class HasRoleTest extends TestCase
{
    public function testUserRoles()
    {
        $user = $this->createUser();

        $roleAdmin = Role::create([
            'name' => 'admin'
        ]);

        $workspace = Workspace::create([
            'name' => 'Workspace Name'
        ]);

        \DB::table('role_user')->insert([
            'user_id' => $user->id,
            'role_id' => $roleAdmin->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertInstanceOf(BelongsToMany::class, $user->roles($workspace));
        $this->assertContains($roleAdmin->id, $user->roles($workspace)->pluck('id'));
        $this->assertEquals(1, $user->roles($workspace)->count());
    }

    public function testUserHasRole()
    {
        $user = $this->createUser();

        Role::create([
            'name' => 'super'
        ]);

        $roleAdmin = Role::create([
            'name' => 'admin'
        ]);

        $workspace = Workspace::create([
            'name' => 'Workspace'
        ]);

        \DB::table('role_user')->insert([
            'user_id' => $user->id,
            'role_id' => $roleAdmin->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertTrue($user->hasRole('admin', $workspace));
        $this->assertTrue($user->hasRole('super|admin', $workspace));
        $this->assertTrue($user->hasRole($roleAdmin, $workspace));
        $this->assertFalse($user->hasRole('super', $workspace));
        $this->assertFalse($user->hasRole('super', $workspace));
    }

    public function testUserAttachRole()
    {
        $user = $this->createUser();

        $roleAdmin = Role::create([
            'name' => 'admin'
        ]);

        $workspace = Workspace::create([
            'name' => 'Workspace'
        ]);

        $user->attachRole($roleAdmin, $workspace);

        $this->assertDatabaseHas('role_user', [
            'role_id' => $roleAdmin->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->expectException(ModelNotFoundException::class);

        $user->attachRole([0], $workspace);
    }

    public function testUserAttachRoles()
    {
        $user = $this->createUser();

        $roleSuper = Role::create([
            'name' => 'super'
        ]);

        $roleAdmin = Role::create([
            'name' => 'admin'
        ]);

        $workspace = Workspace::create([
            'name' => 'Workspace'
        ]);

        $user->attachRole([$roleSuper, $roleAdmin], $workspace);

        $this->assertDatabaseHas('role_user', [
            'role_id' => $roleSuper->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertDatabaseHas('role_user', [
            'role_id' => $roleAdmin->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->expectException(ModelNotFoundException::class);

        $user->attachRole([0], $workspace);
    }

    public function testUserSyncRoles()
    {
        $user = $this->createUser();

        $roleSuper = Role::create([
            'name' => 'super'
        ]);

        $roleAdmin = Role::create([
            'name' => 'admin'
        ]);

        $workspace = Workspace::create([
            'name' => 'Workspace'
        ]);

        \DB::table('role_user')->insert([
            'user_id' => $user->id,
            'role_id' => $roleAdmin->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $user->syncRoles([$roleSuper, $roleAdmin], $workspace);

        $this->assertDatabaseHas('role_user', [
            'role_id' => $roleSuper->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertDatabaseHas('role_user', [
            'role_id' => $roleAdmin->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->expectException(ModelNotFoundException::class);

        $user->syncRoles([0], $workspace);
    }

    public function testUserDetachRoles()
    {
        $user = $this->createUser();

        $roleSuper = Role::create([
            'name' => 'super'
        ]);
        $roleAdmin = Role::create([
            'name' => 'admin'
        ]);

        $workspace = Workspace::create([
            'name' => 'Workspace'
        ]);

        \DB::table('role_user')->insert([
            'user_id' => $user->id,
            'role_id' => $roleSuper->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        \DB::table('role_user')->insert([
            'user_id' => $user->id,
            'role_id' => $roleAdmin->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $otherWorkspace = Workspace::create([
            'name' => 'Workspace'
        ]);

        \DB::table('role_user')->insert([
            'user_id' => $user->id,
            'role_id' => $roleSuper->id,
            'resource_id' => $otherWorkspace->id,
            'resource_type' => Workspace::class
        ]);

        $user->detachRoles([$roleSuper->id, $roleAdmin->id], $workspace);

        $this->assertDatabaseMissing('role_user', [
            'user_id' => $user->id,
            'role_id' => $roleSuper->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertDatabaseMissing('role_user', [
            'user_id' => $user->id,
            'role_id' => $roleAdmin->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
            'role_id' => $roleSuper->id,
            'resource_id' => $otherWorkspace->id,
            'resource_type' => Workspace::class
        ]);
    }
}
