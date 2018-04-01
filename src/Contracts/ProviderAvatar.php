<?php

namespace Exceedone\LaravelAdminOauth\Contracts;
use Laravel\Socialite\Contracts;

interface ProviderAvatar
{
    /**
     * Get the User avatar.
     * @param mixed $token access token if necessary
     */
    public function getAvatar($token = null);
}
