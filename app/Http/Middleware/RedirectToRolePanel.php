<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToRolePanel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    /**
     * Define role-wise landing routes here (scalable).
     * Keys can be role slugs; values can be route names.
     */
    private array $rolePanelRoutes = [
        'employee' => 'employee.dashboard',
        // 'member' => 'member.dashboard',
        // 'admin'  => 'dashboard',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only redirect when user is hitting /dashboard
        if (! $user || ! $request->is('dashboard')) {
            return $next($request);
        }

        // Adjust this part depending on your role implementation
        $role = $user->role ?? null; // if you have a 'role' column

        if ($role && isset($this->rolePanelRoutes[$role])) {
            return redirect()->route($this->rolePanelRoutes[$role]);
        }

        return $next($request);
    }
    
}
