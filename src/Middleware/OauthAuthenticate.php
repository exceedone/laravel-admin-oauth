<?php

namespace Exceedone\LaravelAdminOauth\Middleware;

use Closure;
use Encore\Admin\Admin;
use Illuminate\Support\Facades\Auth;

class OauthAuthenticate extends \Encore\Admin\Middleware\Authenticate
{
    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        $excepts = [
            admin_base_path('auth/login'),
            admin_base_path('auth/logout'),
        ];

        // except "login/callback/{provider}" route.
        $providers = config('adminoauth.login_providers');
        if(!is_array($providers)){$providers = [$providers];}
        foreach ($providers as $provider)
        {
            array_push($excepts, admin_base_path('auth/login/'.$provider));
            array_push($excepts, admin_base_path('auth/login/callback/'.$provider));
        }

        foreach ($excepts as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
