<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantFromUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->tenant_id) {
            $tenant = Tenant::find(auth()->user()->tenant_id);

            if ($tenant && $tenant->active) {
                $tenant->makeCurrent();
            }
        }

        return $next($request);
    }
}
