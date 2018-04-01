<?php

namespace Exceedone\LaravelAdminOauth\Facades;

use Illuminate\Support\Facades\Facade;
use Exceedone\AdminOauth\AdminOauth;

/**
 * Class Admin.
 *
 */
class AdminOauthFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AdminOauth::class;
    }
}
