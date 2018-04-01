 # laravel-admin-oauth
 "laravel-admin-oauth" is an extension package for laravel-admin and login using oauth.

## Screenshot
------------
![img](https://raw.githubusercontent.com/exceedone/laravel-admin-oauth/master/doc/img/screenshot1.png)

## Requirements
------------
 - PHP >= 7.0.0
 - Laravel >= 5.5.0
 - laravel-admin >= 1.5.0
 - Laravel Socialite >=3.0.0

## Installation
------------
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

This application uses the [Laravel Socialite](https://github.com/laravel/socialite) package.
please set up Socialite.

## Caution
------------
laravel-admin-oauth is alpha version.
I'm developing now, so this laravel-admin-oauth has a lot of tasks.