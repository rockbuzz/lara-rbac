<?php

namespace Rockbuzz\LaraRbac;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class RbacMiddleware
{
    public function handle(Request $request, Closure $next, $level, $names)
    {
        if (auth()->guest()) {
            throw new AuthenticationException();
        }

        if (! in_array($level, ['role', 'permission'])) {
            abort(500, 'Invalid RBAC operator specified.');
        }

        if ('permission' === $level) {
            if ($request->user()->hasAnyPermissions(
                explode('|', $names),
                $request->route('group'))
            )
            {
                return $next($request);
            }
        }

        if ('role' === $level) {
            if ($request->user()->hasAnyRoles(
                explode('|', $names),
                $request->route('group'))
            )
            {
                return $next($request);
            }
        }

        abort(403);
    }
}
