<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRbacTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('rbac.tables.prefix');

        Schema::create($prefix . 'roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create($prefix . 'permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create($prefix . 'permission_role', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
            $table->uuid('permission_id');
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
            $table->primary(['permission_id', 'role_id'], 'permission_role_primary');
            $table->index(['permission_id', 'role_id'], 'permission_role_index');
        });

        Schema::create($prefix . 'role_user', function (Blueprint $table) {
            $table->uuid('role_id')->index();
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
            $table->uuid('user_id')->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->uuid('resource_id')->index();
            $table->string('resource_type')->index();
            $table->primary(['user_id', 'role_id', 'resource_id', 'resource_type'], 'role_user_resource_primary');
            $table->index(['role_id', 'user_id', 'resource_id', 'resource_type'], 'role_user_resource_index');
        });

        Schema::create($prefix . 'permission_user', function (Blueprint $table) {
            $table->uuid('permission_id')->index();
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
            $table->uuid('user_id')->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->uuid('resource_id')->index();
            $table->string('resource_type')->index();
            $table->primary(['user_id', 'permission_id', 'resource_id', 'resource_type'], 'permission_user_resource_primary');
            $table->index(['user_id', 'permission_id', 'resource_id', 'resource_type'], 'permission_role_resource_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = config('rbac.tables.prefix');

        Schema::dropIfExists($prefix . 'permission_user');
        Schema::dropIfExists($prefix . 'role_user');
        Schema::dropIfExists($prefix . 'permission_role');
        Schema::dropIfExists($prefix . 'permissions');
        Schema::dropIfExists($prefix . 'roles');
    }
}
