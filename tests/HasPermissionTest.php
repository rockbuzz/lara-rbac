<?php

namespace Tests;

use Tests\Models\Workspace;
use Rockbuzz\LaraRbac\Models\{Role, Permission};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HasPermissionTest extends TestCase
{
    public function testUserPermissions()
    {
        $user = $this->createUser();

        $permissionPostStore = Permission::create(['name' => 'create.post']);

        $workspace = Workspace::create(['name' => 'Workspace']);

        \DB::table('permission_user')->insert([
            'user_id' => $user->id,
            'permission_id' => $permissionPostStore->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertInstanceOf(BelongsToMany::class, $user->permissions());
        $this->assertContains($permissionPostStore->id, $user->permissions()->pluck('id'));
    }

    public function testUserPermissionsForResource()
    {
        $user = $this->createUser();

        $permissionPostStore = Permission::create([
            'name' => 'create.post'
        ]);

        $otherWorkspace = Workspace::create(['name' => 'Other Workspace']);
        $workspace = Workspace::create(['name' => 'Workspace']);

        \DB::table('permission_user')->insert([
            'user_id' => $user->id,
            'permission_id' => $permissionPostStore->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertInstanceOf(BelongsToMany::class, $user->permissions($workspace));
        $this->assertContains($permissionPostStore->id, $user->permissions($workspace)->pluck('id'));
        $this->assertNotContains($permissionPostStore->id, $user->permissions($otherWorkspace)->pluck('id'));
    }

    public function testUserHasPermissions()
    {
        $user = $this->createUser();

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        Permission::create(['name' => 'post.update']);

        $workspace = Workspace::create(['name' => 'Workspace']);

        \DB::table('permission_user')->insert([
            'user_id' => $user->id,
            'permission_id' => $permissionPostStore->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertTrue($user->hasPermission('post.store', $workspace));
        $this->assertTrue($user->hasPermission('post.update|post.store', $workspace));
        $this->assertTrue($user->hasPermission($permissionPostStore, $workspace));
        $this->assertFalse($user->hasPermission('post.update', $workspace));
        $this->assertFalse($user->hasPermission('post.update', $workspace));
    }

    public function testUserHasPermissionsForRole()
    {
        $user = $this->createUser();

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $role = Role::create(['name' => 'admin']);

        \DB::table('permission_role')->insert([
            'role_id' => $role->id,
            'permission_id' => $permissionPostStore->id
        ]);

        $workspace = Workspace::create(['name' => 'Workspace']);

        \DB::table('role_user')->insert([
            'role_id' => $role->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertTrue($user->hasPermission('post.store', $workspace));
        $this->assertTrue($user->hasPermission('post.update|post.store', $workspace));
        $this->assertTrue($user->hasPermission($permissionPostStore, $workspace));
        $this->assertFalse($user->hasPermission('post.update', $workspace));
        $this->assertFalse($user->hasPermission('post.update', $workspace));
    }

    public function testUserAttachPermission()
    {
        $user = $this->createUser();

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $workspace = Workspace::create(['name' => 'Workspace']);

        $user->attachPermission($permissionPostStore, $workspace);

        $this->assertDatabaseHas('permission_user', [
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->expectException(ModelNotFoundException::class);

        $user->attachPermission([0], $workspace);
    }

    public function testUserAttachPermissions()
    {
        $user = $this->createUser();

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $permissionPostUpdate = Permission::create(['name' => 'post.update']);

        $workspace = Workspace::create(['name' => 'Workspace']);

        $user->attachPermission([$permissionPostStore, $permissionPostUpdate], $workspace);

        $this->assertDatabaseHas('permission_user', [
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertDatabaseHas('permission_user', [
            'permission_id' => $permissionPostUpdate->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->expectException(ModelNotFoundException::class);

        $user->attachPermission([0], $workspace);
    }

    public function testUserSyncPermissions()
    {
        $user = $this->createUser();

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $permissionPostUpdate = Permission::create(['name' => 'admin']);

        $workspace = Workspace::create(['name' => 'Workspace']);

        \DB::table('role_user')->insert([
            'user_id' => $user->id,
            'role_id' => $permissionPostUpdate->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $user->syncPermissions([$permissionPostStore, $permissionPostUpdate], $workspace);

        $this->assertDatabaseHas('permission_user', [
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertDatabaseHas('permission_user', [
            'permission_id' => $permissionPostUpdate->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->expectException(ModelNotFoundException::class);

        $user->syncPermissions([0], $workspace);
    }

    public function testUserDetachPermissions()
    {
        $user = $this->createUser();

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $permissionPostUpdate = Permission::create(['name' => 'post.update']);

        $workspace = Workspace::create(['name' => 'Workspace Name']);

        \DB::table('permission_user')->insert([
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        \DB::table('permission_user')->insert([
            'permission_id' => $permissionPostUpdate->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $otherWorkspace = Workspace::create(['name' => 'Workspace']);

        \DB::table('permission_user')->insert([
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => $otherWorkspace->id,
            'resource_type' => Workspace::class
        ]);


        $user->detachPermissions([$permissionPostStore->id, $permissionPostUpdate->id], $workspace);

        $this->assertDatabaseMissing('permission_user', [
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertDatabaseMissing('permission_user', [
            'permission_id' => $permissionPostUpdate->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertDatabaseHas('permission_user', [
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => $otherWorkspace->id,
            'resource_type' => Workspace::class
        ]);
    }
}
