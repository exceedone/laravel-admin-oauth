<?php

namespace Exceedone\LaravelAdminOauth\Contracts;
use Laravel\Socialite\Contracts;

class AdminOauthConfig
{
    protected $config = array(
        'use_dafault_login' => true,
        'automatic_loginpage_provider' => false,
        'upload_storage_driver' => 'local',
        'login_providers' => [],
    );

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        $login_providers = config("adminoauth");
        $this->config = array_intersect_key($login_providers, $this->config) + $this->config;

        // if $providerName is null, show login page.
        $providers = [];
        foreach ($this->config['login_providers'] as $provider)
        {
            $provider_config_default = array(
                'scopes' => null,
                'display_name' => null,
                'background_color' => null,
                'font_color' => null,
                'background_color_hover' => null,
                'font_color_hover' => null,
                'font_owesome' => null,
                'btn_name' => null,
                'user_id_key' => 'id',
                'user_name_key' => 'name',
                'user_mail_key' => 'email',
                'user_avatar_key' => 'avatar',
            );
            $provider_config = config("services.$provider");

            $provider_config = array_intersect_key($provider_config, $provider_config_default) + $provider_config_default;

            // set properties
            if(is_null($provider_config['display_name'])){
                $provider_config['display_name'] = $this->pascalize($provider);
            }
            if(is_null($provider_config['font_owesome'])){
                $provider_config['font_owesome'] = "fa-$provider";
            }
            if(is_null($provider_config['btn_name'])){
                $provider_config['btn_name'] = "btn-$provider";
            }

            $providers[$provider] = $provider_config;
        }

        $this->config['login_providers'] = $providers;
    }

    public function getConfig(){
        return $this->config;
    }

    private function pascalize($string)
    {
        $string = strtolower($string);
        $string = str_replace('_', ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        return $string;
    }
}
