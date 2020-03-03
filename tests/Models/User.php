<?php

namespace Tests\Models;

use Rockbuzz\LaraRbac\Traits\HasRbac;
use Rockbuzz\LaraRbac\Contracts\User as UserInterface;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements UserInterface
{
    use HasRbac;

    protected $guarded = [];
}
