<?php

namespace Exceedone\LaravelAdminOauth\Database;
use Illuminate\Database\Eloquent\Model;

class AdminOauthAccount extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mail',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'provider_name', 'provider_id', 'admin_user_id'
    ];
}
