<?php

namespace Tests\Stubs;

use Rockbuzz\LaraRbac\Traits\HasRbac;
use Rockbuzz\LaraRbac\Contracts\HasRbac as HasRbacInterface;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasRbacInterface
{
    use HasRbac;

    protected $guarded = [];
}
