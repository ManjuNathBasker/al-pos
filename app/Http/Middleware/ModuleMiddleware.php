<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ModuleService;

class ModuleMiddleware
{
    protected $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (!$this->moduleService->isActive($module)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This module is not enabled for your company.'], 403);
            }
            abort(403, 'This module is not enabled for your company.');
        }

        return $next($request);
    }
}
