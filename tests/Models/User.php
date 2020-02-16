<?php

namespace Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Rockbuzz\LaraRbac\Contracts\User as UserInterface;
use Rockbuzz\LaraRbac\Traits\HasRbac;

class User extends Authenticatable implements UserInterface
{
    use HasRbac;

    protected $guarded = [];
}
