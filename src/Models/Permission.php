<?php

namespace Rockbuzz\LaraRbac\Models;

use Rockbuzz\LaraUuid\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use Uuid;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name'
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('rbac.tables.permissions'));
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(config('rbac.models.role'));
    }

    /**
     * @param Role|string $role Instance role or role name or roles name separated by |.
     * ex.: super|admin
     * @return bool
     */
    public function hasRole($role): bool
    {
        $role = is_a($role, Role::class) ? $role->name : $role;

        return $this->roles()
            ->whereIn('name', explode('|', $role))
            ->exists();
    }
}
