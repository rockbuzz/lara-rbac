# Lara RBAC

Role Based Access Control per resource

[![Build Status](https://travis-ci.org/rockbuzz/lara-rbac.svg?branch=master)](https://travis-ci.org/rockbuzz/lara-rbac)

## Requirements

PHP: >=7.3

## Install

```bash
$ composer require rockbuzz/lara-rbac
```

Publish migration and config files

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
$user->attachRole($adminRole, $resource);
```
or 
```php
$user->attachRole($adminRole->id, $resource);
```
or 
```php
$user->attachRole([$adminRole->id, $writerRole->id], $resource);
```

#### Sync roles to user

```php
$user->syncRoles([$adminRole->id, $writerRole->id], $resource);
```

#### Revoke role from user

```php
$user->detachRoles([$adminRole, $writerRole->id], $resource);
```

### Permissions

#### Create permission

```php
$postStore = new \Rockbuzz\LaraRbac\Models\Permission;
$postStore->name = 'post.store';
$postStore->save();

$postUpdate = new \Rockbuzz\LaraRbac\Models\Permission;
$postUpdate->name = 'post.update';
$postUpdate->save();
```

#### Assign permission to role

https://laravel.com/docs/6.x/eloquent-relationships

#### Assign permission to user

```php
$user = User::find(1);
$user->attachPermission($postStore, $resource);
```
or
```php
$user->attachPermission($postStore->id, $resource);
```
or
```php
$user->attachPermission([$postStore->id, $postUpdate->id], $resource);
```

#### Sync permissions to user

```php
$user->syncPermissions([$postStore->id, $postUpdate->id], $resource);
```

#### Revoke permission from user

```php
$user->detachPermissions([$postStore->id, $postUpdate->id], $resource);
```

### Check user roles/permissions

```php
auth()->user()->hasRole('admin', $resource);
auth()->user()->hasRole('admin|writer', $resource);
auth()->user()->hasPermission('post.update', $resource);
auth()->user()->hasPermission('post.update|delete.post', $resource);
```

### Blade directive

Check for role

```
@hasrole('admin', $resource)
    // ok
@else
    // no
@endrole
```

Check for permission

```
@haspermission('post.store|post.update', $resource)
    // ok
@else
    // no
@endpermission
```

## License

The Lara RBAC is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).