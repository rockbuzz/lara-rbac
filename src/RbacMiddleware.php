<?php

namespace Rockbuzz\LaraRbac;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class RbacMiddleware
{
    public function handle(Request $request, Closure $next, string $level, string $names)
    {
        if (auth()->guest()) {
            throw new AuthenticationException();
        }

        if (! in_array($level, ['role', 'permission'])) {
            abort(500, 'Invalid RBAC operator specified.');
        }

        if ('permission' === $level) {
            if ($request->user()->hasPermission($names, $request->route('group'))) {
                return $next($request);
            }
        }

        if ('role' === $level) {
            if ($request->user()->hasRole($names, $request->route('group'))) {
                return $next($request);
            }
        }

        abort(403);
    }
}
