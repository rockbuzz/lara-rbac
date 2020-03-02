<?php

use Rockbuzz\LaraRbac\Models\{Role, Permission};

return [
    'models' => [
        'role' => Role::class,
        'permission' => Permission::class,
    ],
    'tables' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'permission_role' => 'permission_role',
        'role_user' => 'role_user',
        'permission_user' => 'permission_user',
        'morph_columns' => [
            'id' => 'resource_id',
            'type' => 'resource_type'
        ]
    ]
];
