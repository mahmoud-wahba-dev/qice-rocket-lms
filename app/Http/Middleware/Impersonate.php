<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class Impersonate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $isImpersonating = false;
        $impersonatedName = null;
        $impersonatedRoleLabel = null;

        if (session()->has('impersonated')) {
            // Leaving: keep the real session user (admin). onceUsingId would hide them
            // and panel middleware / leave restore would fail.
            if (!$request->routeIs('panel.v1.leave-impersonation')) {
                $targetId = (int) session()->get('impersonated');
                // onceUsingId swaps the request user without destroying the admin session.
                if ($targetId > 0 && Auth::onceUsingId($targetId)) {
                    $isImpersonating = true;
                    $user = Auth::user();
                    $impersonatedName = optional($user)->full_name
                        ?: optional($user)->email
                        ?: ('#' . $targetId);
                    $impersonatedRoleLabel = match ((string) optional($user)->role_name) {
                        \App\Models\Role::$teacher => 'مدرب',
                        \App\Models\Role::$organization => 'منظمة',
                        \App\Models\Role::$user => 'طالب',
                        default => 'مستخدم',
                    };
                } else {
                    session()->forget(['impersonated', 'impersonator_id', 'impersonation_return_tab']);
                }
            }
        }

        View::share('isImpersonating', $isImpersonating);
        View::share('impersonatedName', $impersonatedName);
        View::share('impersonatedRoleLabel', $impersonatedRoleLabel);
        View::share('leaveImpersonationUrl', $isImpersonating
            ? route('panel.v1.leave-impersonation')
            : null);

        return $next($request);
    }
}
