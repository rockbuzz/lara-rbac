<?php

namespace Tests;

use Tests\Stubs\User;
use Tests\Stubs\Workspace;
use Illuminate\Support\Facades\DB;
use Rockbuzz\LaraRbac\Models\{Role, Permission};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function testUserPermissions()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        DB::table('permission_user')->insert([
            'user_id' => $user->id,
            'permission_id' => $permissionPostStore->id,
            'resource_id' => null,
            'resource_type' => null
        ]);

        $this->assertInstanceOf(BelongsToMany::class, $user->permissions());
        $this->assertContains($permissionPostStore->id, $user->permissions()->pluck('id'));
    }

    public function testUserPermissionsWithResource()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $workspace = Workspace::create(['name' => 'Workspace']);

        DB::table('permission_user')->insert([
            'user_id' => $user->id,
            'permission_id' => $permissionPostStore->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->assertInstanceOf(BelongsToMany::class, $user->permissions($workspace));
        $this->assertContains($permissionPostStore->id, $user->permissions($workspace)->pluck('id'));
    }

    public function testUserPermissionsForResource()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create([
            'name' => 'post.store'
        ]);

        $otherWorkspace = Workspace::create(['name' => 'Other Workspace']);
        $workspace = Workspace::create(['name' => 'Workspace']);

        DB::table('permission_user')->insert([
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
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        Permission::create(['name' => 'post.update']);

        DB::table('permission_user')->insert([
            'user_id' => $user->id,
            'permission_id' => $permissionPostStore->id,
            'resource_id' => null,
            'resource_type' => null
        ]);

        $this->assertTrue($user->hasPermission('post.store'));
        $this->assertTrue($user->hasPermission('post.update|post.store'));
        $this->assertTrue($user->hasPermission($permissionPostStore));
        $this->assertFalse($user->hasPermission('post.update'));
        $this->assertFalse($user->hasPermission('post.update'));
    }

    public function testUserHasPermissionsWithResource()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        Permission::create(['name' => 'post.update']);

        $workspace = Workspace::create(['name' => 'Workspace']);

        DB::table('permission_user')->insert([
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
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $role = Role::create(['name' => 'admin']);

        DB::table('permission_role')->insert([
            'role_id' => $role->id,
            'permission_id' => $permissionPostStore->id
        ]);

        $workspace = Workspace::create(['name' => 'Workspace']);

        DB::table('role_user')->insert([
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

    public function testUserAttachPermissionWithInstance()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $user->attachPermission($permissionPostStore);

        $this->assertDatabaseHas('permission_user', [
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => null,
            'resource_type' => null
        ]);

        $this->expectException(ModelNotFoundException::class);

        $user->attachPermission([0]);
    }

    public function testUserAttachPermissionWithInstanceAndResource()
    {
        $user = $this->create(User::class);

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

    public function testUserAttachPermissionWithId()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $workspace = Workspace::create(['name' => 'Workspace']);

        $user->attachPermission($permissionPostStore->id, $workspace);

        $this->assertDatabaseHas('permission_user', [
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->expectException(ModelNotFoundException::class);

        $user->attachPermission([0], $workspace);
    }

    public function testUserAttachPermissionsWithInstance()
    {
        $user = $this->create(User::class);

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

    public function testUserAttachPermissionsWithId()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $permissionPostUpdate = Permission::create(['name' => 'post.update']);

        $workspace = Workspace::create(['name' => 'Workspace']);

        $user->attachPermission([$permissionPostStore->id, $permissionPostUpdate->id], $workspace);

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

    public function testUserSyncPermissionsWithInstance()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $permissionPostUpdate = Permission::create(['name' => 'admin']);

        DB::table('permission_user')->insert([
            'user_id' => $user->id,
            'permission_id' => $permissionPostUpdate->id,
            'resource_id' => null,
            'resource_type' => null
        ]);

        $user->syncPermissions([$permissionPostStore, $permissionPostUpdate]);

        $this->assertDatabaseHas('permission_user', [
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => null,
            'resource_type' => null
        ]);

        $this->assertDatabaseHas('permission_user', [
            'permission_id' => $permissionPostUpdate->id,
            'user_id' => $user->id,
            'resource_id' => null,
            'resource_type' => null
        ]);

        $this->expectException(ModelNotFoundException::class);

        $user->syncPermissions([0]);
    }

    public function testUserSyncPermissionsWithInstanceAndResource()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $permissionPostUpdate = Permission::create(['name' => 'admin']);

        $workspace = Workspace::create(['name' => 'Workspace']);

        DB::table('permission_user')->insert([
            'user_id' => $user->id,
            'permission_id' => $permissionPostUpdate->id,
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

    public function testUserSyncPermissionsWithId()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $permissionPostUpdate = Permission::create(['name' => 'admin']);

        $workspace = Workspace::create(['name' => 'Workspace']);

        DB::table('permission_user')->insert([
            'user_id' => $user->id,
            'permission_id' => $permissionPostUpdate->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $user->syncPermissions([$permissionPostStore->id, $permissionPostUpdate->id], $workspace);

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
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $permissionPostUpdate = Permission::create(['name' => 'post.update']);

        DB::table('permission_user')->insert([
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => null,
            'resource_type' => null
        ]);

        DB::table('permission_user')->insert([
            'permission_id' => $permissionPostUpdate->id,
            'user_id' => $user->id,
            'resource_id' => null,
            'resource_type' => null
        ]);

        $otherWorkspace = Workspace::create(['name' => 'Workspace']);

        DB::table('permission_user')->insert([
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => null,
            'resource_type' => null
        ]);


        $user->detachPermissions([$permissionPostStore->id, $permissionPostUpdate->id]);

        $this->assertDatabaseMissing('permission_user', [
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => null,
            'resource_type' => null
        ]);

        $this->assertDatabaseMissing('permission_user', [
            'permission_id' => $permissionPostUpdate->id,
            'user_id' => $user->id,
            'resource_id' => null,
            'resource_type' => null
        ]);
    }

    public function testUserDetachPermissionsWithResource()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $permissionPostUpdate = Permission::create(['name' => 'post.update']);

        $workspace = Workspace::create(['name' => 'Workspace Name']);

        DB::table('permission_user')->insert([
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        DB::table('permission_user')->insert([
            'permission_id' => $permissionPostUpdate->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $otherWorkspace = Workspace::create(['name' => 'Workspace']);

        DB::table('permission_user')->insert([
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

    public function testUserHasPermissionUniqueInDatabase()
    {
        $this->markTestSkipped();
        
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        DB::table('permission_user')->insert([
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => null,
            'resource_type' => null
        ]);

        $this->expectException(\PDOException::class);

        DB::table('permission_user')->insert([
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => null,
            'resource_type' => null
        ]);
    }

    public function testUserHasPermissionUniqueInDatabaseWithResource()
    {
        $user = $this->create(User::class);

        $permissionPostStore = Permission::create(['name' => 'post.store']);

        $workspace = Workspace::create(['name' => 'Workspace Name']);

        DB::table('permission_user')->insert([
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);

        $this->expectException(\PDOException::class);

        DB::table('permission_user')->insert([
            'permission_id' => $permissionPostStore->id,
            'user_id' => $user->id,
            'resource_id' => $workspace->id,
            'resource_type' => Workspace::class
        ]);
    }
}
