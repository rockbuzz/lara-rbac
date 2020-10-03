<?php

use Rockbuzz\LaraRbac\Models\{Role, Permission};

return [
    'models' => [
        'role' => Role::class,
        'permission' => Permission::class,
        'user' => App\User::class
    ],
    'tables' => [
        'role_user' => 'role_user',
        'permission_user' => 'permission_user',
        'users' => 'users',
        'morph_columns' => [
            'id' => 'resource_id',
            'type' => 'resource_type'
        ]
    ]
];
