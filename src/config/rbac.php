<?php

use Rockbuzz\LaraRbac\Models\{Role, Permission};

return [
    'models' => [
        'role' => Role::class,
        'permission' => Permission::class,
        'user' => App\User::class
    ]
];
