<?php

namespace Tests;

use Rockbuzz\LaraUuid\Traits\Uuid;
use Rockbuzz\LaraRbac\Models\Role;
use Rockbuzz\LaraRbac\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RoleTest extends TestCase
{
    protected $role;

    public function setUp(): void
    {
        parent::setUp();

        $this->role = new Role();
    }

    public function testIfUsesTraits()
    {
        $this->assertEquals(
            [
                Uuid::class
            ],
            array_values(class_uses(Permission::class))
        );
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->role->incrementing);
    }

    public function testKeyType()
    {
        $this->assertEquals('string', $this->role->getKeyType());
    }
    public function testRoleFillable()
    {
        $role = Role::create([
            'name' => 'admin'
        ]);

        $this->assertEquals(['name'], $role->getFillable());
    }

    public function testCasts()
    {
        $this->assertEquals(['id' => 'string'], $this->role->getCasts());
    }

    public function testDates()
    {
        $this->assertEquals(
            array_values(['created_at', 'updated_at']),
            array_values($this->role->getDates())
        );
    }

    public function testRoleCanHavePermissions()
    {
        $role = Role::create([
            'name' => 'admin'
        ]);

        $permission = Permission::create([
            'name' => 'post.create'
        ]);

        \DB::table('permission_role')->insert([
            'permission_id' => $permission->id,
            'role_id' => $role->id
        ]);

        $this->assertInstanceOf(BelongsToMany::class, $role->permissions());
        $this->assertEquals(1, $role->permissions->count());
        $this->assertContains($permission->id, $role->permissions->pluck('id'));
    }

    public function testRoleHasPermission()
    {
        $role = Role::create([
            'name' => 'admin'
        ]);

        $permission = Permission::create([
            'name' => 'post.create'
        ]);

        $permissionUp = Permission::create([
            'name' => 'post.update'
        ]);

        \DB::table('permission_role')->insert([
            'permission_id' => $permission->id,
            'role_id' => $role->id
        ]);

        $this->assertTrue($role->hasPermission($permission));
        $this->assertTrue($role->hasPermission($permission->name));
        $this->assertFalse($role->hasPermission($permissionUp));
        $this->assertFalse($role->hasPermission($permissionUp->name));

        $permissionDel = Permission::create([
            'name' => 'post.delete'
        ]);

        \DB::table('permission_role')->insert([
            'permission_id' => $permissionDel->id,
            'role_id' => $role->id
        ]);

        $this->assertTrue($role->hasPermission("{$permissionUp->name}|{$permissionDel->name}"));
    }
}
