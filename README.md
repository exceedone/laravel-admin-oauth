 # laravel-admin-oauth
 "laravel-admin-oauth" is an extension package for laravel-admin and login using oauth.

## Screenshot
![img](https://raw.githubusercontent.com/exceedone/laravel-admin-oauth/master/doc/img/screenshot1.png)

## Requirements
 - PHP >= 7.0.0
 - Laravel >= 5.5.0
 - laravel-admin >= 1.5.0
 - Laravel Socialite >=3.0.0

## Installation
First, install laravel 5.5, and install laravel-admin 1.5, and make sure that the database connection settings are correct.

Second, finish laravel-admin command "php artisan admin:install". Please read this url.
[laravel-admin](https://github.com/z-song/laravel-admin)

After, install laravel-admin-oauth. *Now preparing composer.
```
composer require exceedone/laravel-admin-oauth
```

Then run these commands to publish assets and config：

```
php artisan vendor:publish --provider="Exceedone\LaravelAdminOauth\AdminOauthServiceProvider"
```

At last run following command to finish install. 
```
php artisan adminoauth:install
```

## Setup
### Socialite
This application uses the [Laravel Socialite](https://github.com/laravel/socialite) package.
please set up Socialite.

``` config/services.php
    'google' => [
        'client_id'     => 'XXXXXXX.apps.googleusercontent.com',
        'client_secret' => 'YYYYYYY',
        'redirect'      => 'http://localhost/admin/auth/login/callback/google',
    ],
    'facebook' => [
        'client_id'     => '123456789',
        'client_secret' => '1112223334445555666',
        'redirect'      => 'http://localhost/admin/auth/login/callback/facebook',
    ],
    'github' => [
        'client_id'     => 'ABCDEFGH',
        'client_secret' => 'abcdefghijklmn',
        'redirect'      => 'http://localhost/admin/auth/login/callback/github',
        'user_name_key' => 'nickname',
    ],
```

Please write "client_id" "client_secret".
"redirect" is the URL http(s)://(your admin url)/auth/login/callback/(provider_name)

Now writing about option setting.

### Setting Laravl Admin Oauth

``` config/adminoauth.php
return [
    /*
      * use default login.
      * if "true", show default login form.
      * if "false", hide default login form. only show oauth provider buttons.
      */
    'use_dafault_login' => true,

    /*
      * if user accesses login page, redirect provider's login page.
      * if "true", use first item of "adminoauth.login_providers".
      */
    'automatic_loginpage_provider' => false,

    /*
     * showing OAuth provider list for login
     */
    'login_providers' => ['google', 'facebook', 'github'],
];
```

"login_providers" is list of oauth signin providers.
please write the same name in "config/services.php".


## Caution
laravel-admin-oauth is alpha version.
I'm developing now, so this laravel-admin-oauth has a lot of tasks.