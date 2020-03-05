<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRbacTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = $this->getConfigTables();

        Schema::create($tables['roles'], function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create($tables['permissions'], function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create($tables['permission_role'], function (Blueprint $table) use ($tables) {
            $table->uuid('role_id')->index();
            $table->foreign('role_id')
                ->references('id')
                ->on($tables['roles'])
                ->onDelete('cascade');
            $table->uuid('permission_id')->index();
            $table->foreign('permission_id')
                ->references('id')
                ->on($tables['permissions'])
                ->onDelete('cascade');
            $table->unique(['permission_id', 'role_id'], 'permission_role_unique');
            $table->index(['permission_id', 'role_id'], 'permission_role_index');
        });

        Schema::create($tables['role_user'], function (Blueprint $table) use ($tables) {
            $table->uuid('role_id')->index();
            $table->foreign('role_id')
                ->references('id')
                ->on($tables['roles'])
                ->onDelete('cascade');
            $table->uuid('user_id')->index();
            $table->foreign('user_id')
                ->references('id')
                ->on($tables['users'])
                ->onDelete('cascade');
            $table->uuid(config('rbac.tables.morph_columns.id', 'resource_id'))->index();
            $table->string(config('rbac.tables.morph_columns.type', 'resource_type'))->index();
            $table->unique([
                'role_id',
                'user_id',
                config('rbac.tables.morph_columns.id', 'resource_id'),
                config('rbac.tables.morph_columns.type', 'resource_type')
            ], 'role_user_resource_unique');
            $table->index([
                'role_id',
                'user_id',
                config('rbac.tables.morph_columns.id', 'resource_id'),
                config('rbac.tables.morph_columns.type', 'resource_type')
            ], 'role_user_resource_index');
        });

        Schema::create($tables['permission_user'], function (Blueprint $table) use ($tables) {
            $table->uuid('permission_id')->index();
            $table->foreign('permission_id')
                ->references('id')
                ->on($tables['permissions'])
                ->onDelete('cascade');
            $table->uuid('user_id')->index();
            $table->foreign('user_id')
                ->references('id')
                ->on($tables['users'])
                ->onDelete('cascade');
            $table->uuid(config('rbac.tables.morph_columns.id', 'resource_id'))->index();
            $table->string(config('rbac.tables.morph_columns.type', 'resource_type'))->index();
            $table->unique([
                'permission_id',
                'user_id',
                config('rbac.tables.morph_columns.id', 'resource_id'),
                config('rbac.tables.morph_columns.type', 'resource_type')
            ], 'permission_user_resource_unique');
            $table->index([
                'permission_id',
                'user_id',
                config('rbac.tables.morph_columns.id', 'resource_id'),
                config('rbac.tables.morph_columns.type', 'resource_type')
            ], 'permission_role_resource_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = $this->getConfigTables();

        Schema::dropIfExists($tables['permission_user']);
        Schema::dropIfExists($tables['role_user']);
        Schema::dropIfExists($tables['permission_role']);
        Schema::dropIfExists($tables['permissions']);
        Schema::dropIfExists($tables['roles']);
    }

    private function getConfigTables(): array
    {
        return config('rbac.tables');
    }
}
