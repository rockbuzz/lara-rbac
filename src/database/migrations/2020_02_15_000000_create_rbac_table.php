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
        Schema::create(config('rbac.tables.prefix') . 'roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create(config('rbac.tables.prefix') . 'permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create(config('rbac.tables.prefix') . 'permission_role', function (Blueprint $table) {
            $table->uuid('role_id')->index();
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('cascade');
            $table->uuid('permission_id')->index();
            $table->foreign('permission_id')
                  ->references('id')
                  ->on('permissions')
                  ->onDelete('cascade');
        });

        Schema::create(config('rbac.tables.prefix') . 'role_user', function (Blueprint $table) {
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
            $table->unique(['user_id', 'role_id', 'resource_id', 'resource_type']);
        });

        Schema::create(config('rbac.tables.prefix') . 'permission_user', function (Blueprint $table) {
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
            $table->unique(['user_id', 'permission_id', 'resource_id', 'resource_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('rbac.tables.prefix') . 'user_permission');
        Schema::dropIfExists(config('rbac.tables.prefix') . 'user_role');
        Schema::dropIfExists(config('rbac.tables.prefix') . 'permission_role');
        Schema::dropIfExists(config('rbac.tables.prefix') . 'permissions');
        Schema::dropIfExists(config('rbac.tables.prefix') . 'roles');
    }
}
