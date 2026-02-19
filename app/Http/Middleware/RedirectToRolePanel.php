<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class RedirectToRolePanel
{
    private array $rolePanelRoutes = [
        'employee' => 'employee.dashboard',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('dashboard*')) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        foreach ($this->rolePanelRoutes as $roleName => $routeName) {
            if ($user->hasRole($roleName)) {
                $url = route($routeName);

                // Inertia visit
                if ($request->header('X-Inertia')) {
                    return Inertia::location($url);
                }

                // Normal request
                return redirect()->to($url);
            }
        }

        return $next($request);
    }
}
