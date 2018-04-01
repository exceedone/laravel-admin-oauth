<?php

namespace Exceedone\LaravelAdminOauth;

use Closure;
use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Navbar;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Exceedone\LaravelAdminOauth\Controllers;

/**
 * Class Admin.
 */
class AdminOauth extends \Encore\Admin\Admin
{
    /**
     * Register the auth routes.
     *
     * @return void
     */
    public static function registerAuthRoutesOauth()
    {
        $attributes = [
            'prefix'     => config('admin.route.prefix'),
            'namespace'  => 'Exceedone\LaravelAdminOauth\Controllers',
            'middleware' => config('admin.route.middleware'),
        ];

        Route::group($attributes, function ($router) {

            /* @var \Illuminate\Routing\Router $router */
            $router->group([], function ($router) {

                /* @var \Illuminate\Routing\Router $router */
                $router->resource('auth/users', 'UserController');
                //$router->resource('auth/roles', 'RoleController');
                //$router->resource('auth/permissions', 'PermissionController');
                //$router->resource('auth/menu', 'MenuController', ['except' => ['create']]);
                //$router->resource('auth/logs', 'LogController', ['only' => ['index', 'destroy']]);
            });

            $router->get('auth/login', 'AdminOauthController@getLoginOauth');
            $router->get('auth/login/{providerName}', 'AdminOauthController@getLoginOauth');
            $router->get('auth/login/callback/{providerName}', 'AdminOauthController@getLoginProviderCallback');
            $router->post('auth/login', 'AdminOauthController@postLogin');
            //$router->get('auth/logout', 'AdminOauthController@getLogout');
            //$router->get('auth/logoutcallback', 'AdminOauthController@getLogoutCallback');
            $router->get('auth/setting', 'AdminOauthController@getSetting');
            $router->put('auth/setting', 'AdminOauthController@putSetting');
        });
    }
}
