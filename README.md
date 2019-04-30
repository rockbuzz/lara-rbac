# Lara RBAC

Role Based Access Control with grouping

[![Build Status](https://travis-ci.org/rockbuzz/lara-rbac.svg?branch=master)](https://travis-ci.org/rockbuzz/lara-rbac)

## Requirements

PHP: >=7.1

## Install

```bash
$ composer require rockbuzz/lara-rbac
```

## Configuration
```php

```

Publish migration files

```
$ php artisan vendor:publish --provider="Rockbuzz\LaraRbac\ServiceProvider"
$ php artisan migrate
```

Add Rbac trait to your `User` model

```php
use Rockbuzz\LaraRbac\Contracts\User as UserInterface;
use Rockbuzz\LaraRbac\Traits\HasRbac;
	
class User extends Authenticatable implements UserInterface
{
    use HasRbac;
    ...
	    
}
```

## Usage

### Roles

#### Create role

```php
$adminRole = new \Rockbuzz\LaraRbac\Models\Role;
$adminRole->name = 'admin';
$adminRole->save();

$writerRole = new \Rockbuzz\LaraRbac\Models\Role;
$writerRole->name = 'writer';
$writerRole->save();
```

#### Assign role to user
	
```php
$user = User::find(1);
$user->attachRole($adminRole, $group = 'group-name');
```
or 
```php
$user->attachRole($adminRole->id, $group = 'group-name');
```

#### Revoke role from user

```php
$user->detachRole([$adminRole, $writerRole->id], $group = 'group-name');
```

### Permissions

#### Create permission

```php
$createPost = new \Rockbuzz\LaraRbac\Models\Permission;
$createPost->name = 'create.post';
$createPost->save();

$updatePost = new \Rockbuzz\LaraRbac\Models\Permission;
$updatePost->name = 'update.post';
$updatePost->save();
```

#### Assign permission to role

https://laravel.com/docs/5.8/eloquent-relationships

#### Assign permission to user

```php
$user = User::find(1);
$user->attachPermission($createPost, $group = 'group-name');
```
or
```php
$user->attachPermission($createPost->id, $group = 'group-name');
```

#### Sync permissions to user

```php
$adminRole->syncPermission([$createPost->id, $updatePost->id], $group = 'group-name');
```

#### Revoke permission from user

```php
$adminRole->detachPermission([$createPost->id, $updatePost->id], $group = 'group-name');
```

### Check user roles/permissions

```php
auth()->user()->hasRole('admin', $group = 'group-name');
auth()->user()->hasRole('admin|writer', $group = 'group-name');
auth()->user()->hasPermission('update.post', $group = 'group-name');
auth()->user()->hasPermission('update.post|delete.post', $group = 'group-name');
```

### Add RBAC middleware to your `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
    ...
    'rbac' => '\Rockbuzz\LaraRbac\RbacMiddleware::class'
];
```

```php
Route::get('/posts/{group?}', [
    'uses' => 'PostsController@index',
    'middleware' => ['auth', 'rbac:role,admin']
]);
Route::get('/posts/{group?}', [
    'uses' => 'PostsController@delete',
    'middleware' => ['auth', 'rbac:permission,delete.post']
]);
```

## License

The Lara RBAC is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).