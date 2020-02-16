<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Rockbuzz\LaraRbac\Traits\Uuid;

class Workspace extends Model
{
    use Uuid;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
    ];
}
