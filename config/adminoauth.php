<?php

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
      * storage driver name for uploading avatar.
      */
    'upload_storage_driver' => 'admin',

    /*
     * showing OAuth provider list for login
     */
    'login_providers' => ['google', 'facebook', 'graph'],
];
