<?php

namespace Tests;

use Illuminate\Support\Facades\DB;
use Rockbuzz\LaraRbac\Models\Role;
use Rockbuzz\LaraRbac\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PermissionTest extends TestCase
{
    protected $permission;

    public function setUp(): void
    {
        parent::setUp();

        $this->permission = new Permission();
    }

    public function testPermissionFillable()
    {
        $role = Permission::create([
            'name' => 'admin'
        ]);

        $this->assertEquals(['name'], $role->getFillable());
    }

    public function testDates()
    {
        $this->assertEquals(
            array_values(['created_at', 'updated_at']),
            array_values($this->permission->getDates())
        );
    }

    public function testPermissionCanHaveRoles()
    {
        $role = Role::create([
            'name' => 'admin'
        ]);

        $permission = Permission::create([
            'name' => 'post.create'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $permission->id,
            'role_id' => $role->id
        ]);

        $this->assertInstanceOf(BelongsToMany::class, $permission->roles());
        $this->assertEquals(1, $permission->roles->count());
        $this->assertContains($role->id, $permission->roles->pluck('id'));
    }

    public function testPermissionHasPermission()
    {
        $role = Role::create([
            'name' => 'super'
        ]);

        $roleAdmin = Role::create([
            'name' => 'admin'
        ]);

        $permission = Permission::create([
            'name' => 'post.create'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $permission->id,
            'role_id' => $role->id
        ]);

        $this->assertTrue($permission->hasRole($role));
        $this->assertTrue($permission->hasRole($role->name));
        $this->assertFalse($permission->hasRole($roleAdmin));
        $this->assertFalse($permission->hasRole($roleAdmin->name));

        $roleEditor = Role::create([
            'name' => 'editor'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $permission->id,
            'role_id' => $roleEditor->id
        ]);

        $this->assertTrue($permission->hasRole("{$roleAdmin->name}|{$roleEditor->name}"));
    }

    public function testPermissionHasRolesUniqueInDatabase()
    {
        $role = Role::create([
            'name' => 'admin'
        ]);

        $permission = Permission::create([
            'name' => 'post.create'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $permission->id,
            'role_id' => $role->id
        ]);

        $this->expectException(\PDOException::class);

        DB::table('permission_role')->insert([
            'permission_id' => $permission->id,
            'role_id' => $role->id
        ]);
    }
}
