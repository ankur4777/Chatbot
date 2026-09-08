<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return redirect()->route('agent.login');
        }

        if (
            $user->role !== 'agent'
            || ! $user->status
            || ! $user->company_id
            || ! $user->company?->status
        ) {
            abort(403);
        }

        return $next($request);
    }
}
