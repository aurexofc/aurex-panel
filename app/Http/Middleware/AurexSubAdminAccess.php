<?php

namespace Pterodactyl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Asif OFC Protection — sub-admin access control.
 *
 * - Super admins (root_admin): full access, untouched.
 * - Sub-admins (is_sub_admin): read-only access to whitelisted routes
 *   (server list, server view details). Everything else is denied with
 *   the signature "Access denied by Asif OFC protection" message.
 * - Everyone else: no admin access at all.
 */
class AurexSubAdminAccess
{
    /**
     * Route names sub-admins are allowed to visit (read-only).
     */
    protected array $allowedRoutes = [
        'admin.servers',
        'admin.servers.view',
        'admin.servers.view.details',
        'admin.servers.view.build',
        'admin.servers.view.startup',
        'admin.servers.view.database',
        'admin.servers.view.mounts',
        'admin.aurex',
        'admin.aurex.overview',
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user) {
            throw new HttpException(403, '⛔ Access denied by Asif OFC protection');
        }

        // Super admin — full access.
        if ($user->root_admin) {
            return $next($request);
        }

        // Sub-admin — only whitelisted read routes, GET only.
        if ($user->is_sub_admin) {
            $routeName = $request->route()?->getName();
            if ($request->isMethod('get') && in_array($routeName, $this->allowedRoutes, true)) {
                return $next($request);
            }

            throw new HttpException(403, '⛔ Access denied by Asif OFC protection');
        }

        throw new HttpException(403, '⛔ Access denied by Asif OFC protection');
    }
}
