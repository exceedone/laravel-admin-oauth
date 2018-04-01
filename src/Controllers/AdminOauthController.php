<?php

namespace Exceedone\LaravelAdminOauth\Controllers;

use GuzzleHttp\Client;
use Exceedone\LaravelAdminOauth\Contracts\AdminOauthConfig;
use Exceedone\LaravelAdminOauth\Database\AdminOauthAccount;
use Exceedone\LaravelAdminOauth\Database\AdminOauthRoleSetting;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class AdminOauthController extends \Encore\Admin\Controllers\AuthController
{
    /**
     * Login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLoginOauth(Request $request, string $providerName = null)
    {
        if (!Auth::guard('admin')->guest()) {
            return redirect(config('admin.route.prefix'));
        }

        $login_providers = config("adminoauth.login_providers");
        if(!is_array($login_providers)){
            $login_providers = [$login_providers];
        }
        // if $providerName is not null, check contains "adminoauth.login_providers" array.
        if(!is_null($providerName)){
            $providers = $login_providers;
            if(!in_array($providerName, $providers)){
                abort(404);
            }
        }

        // if config "adminoauth.automatic_loginpage_provider" is true,
        else if(config("adminoauth.automatic_loginpage_provider")){
            $providerName = config("adminoauth.login_providers")[0];
        }

        $config = (new AdminOauthConfig)->getConfig();
        // if $providerName is not null, redirect provider login page.
        if(!is_null($providerName)){
            $socialiteProvider = $this->getSocialiteProvider($providerName);
            // CAUTION::
            // it's only stateless, laravel has bugs about session
            return $socialiteProvider->redirect();
        }

        return view('adminoauth::login', ['providers' =>$config]);
    }

    /**
     * Login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLoginProviderCallback(Request $request, string $providerName)
    {
        try {
            $config = (new AdminOauthConfig)->getConfig();
            $providerConfig = $config['login_providers'][$providerName];
            $socialiteProvider = $this->getSocialiteProvider($providerName);
            $loginUser = $socialiteProvider->user();

            // get user value
            $id = $loginUser->{$providerConfig['user_id_key']};
            $name = $loginUser->{$providerConfig['user_name_key']};
            $mail = $loginUser->{$providerConfig['user_mail_key']};
            $avatar = $loginUser->{$providerConfig['user_avatar_key']};

            // check user role setting. --------------------------------------------------
            $roleSetting = $this->getAdminOuthRoleSetting($mail);
            if(is_null($roleSetting)){
                //if unauthorized user, show error message
                admin_toastr(trans('adminoauth.unauthorized'), 'error');

                return redirect()->intended(config('admin.route.prefix'));
            }

            // get user avatar --------------------------------------------------
            $stream = null;
            $file = null;

            DB::beginTransaction();
            try
            {
                // if socialiteProvider implements ProviderAvatar, call getAvatar
                if(is_subclass_of($socialiteProvider, "Exceedone\LaravelAdminOauth\Contracts\ProviderAvatar")){
                    $stream = $socialiteProvider->getAvatar($loginUser->token);
                }
                // if user obj has avatar, download avatar.
                else if(!is_null($avatar)){
                    $client = new Client();
                    $response = $client->request('GET', $avatar);
                    $stream = $response->getBody()->getContents();
                }
                // file upload.
                if($stream != null){
                    $file = "avatar/".$id;
                    Storage::disk($config['upload_storage_driver'])->put($file, $stream);
                }

                // Update user data
                $isCreate = false;
                $user = Administrator::
                    join('admin_oauth_accounts', 'admin_oauth_accounts.admin_user_id', 'admin_users.id')
                    ->where(['provider_name' => $providerName, 'provider_id' => $id])
                    ->first();
                if(!$user){
                    $user = new Administrator;
                    $user->created_at = \Carbon\Carbon::now();
                    $user->username = $id;
                    $isCreate = true;
                }
                $user->password = bcrypt($id);
                $user->name = $name;
                $user->avatar = $file;
                $user->updated_at = \Carbon\Carbon::now();
                if($isCreate){ $user->saveOrFail(); }
                else{ $user->update(); }
                $userid = $user->id;

                // insert or update admin_oauth_users
                $oauth_user = AdminOauthAccount::firstOrNew(['provider_name' => $providerName, 'provider_id' => $id]);
                $oauth_user->admin_user_id = $userid;
                $oauth_user->provider_name = $providerName;
                $oauth_user->provider_id = $id;
                $oauth_user->mail = $mail;
                $oauth_user->updated_at = \Carbon\Carbon::now();
                if(!$oauth_user->exists){
                    $oauth_user->created_at = \Carbon\Carbon::now();
                }
                $oauth_user->saveOrFail();

                // Insert user role
                $role = DB::table('admin_role_users')
                        ->where('user_id', $id)
                        ->select('role_id')
                        ->first();
                if($role == null){
                    DB::table('admin_role_users')
                            ->insert(
                            ['role_id' => $roleSetting->admin_role_id
                                , 'user_id' => $userid
                                , 'created_at' => \Carbon\Carbon::now()
                                , 'updated_at' => \Carbon\Carbon::now()
                            ]
                        );
                }
                DB::commit();
            }
            catch (Exception $exception)
            {
                DB::rollback();
                redirect(config('admin.route.prefix'));
            }

            // Auth
            $credentials = [
                'username' => $id
                , 'password' => $id
            ];

            if (Auth::guard('admin')->attempt($credentials)) {
                admin_toastr(trans('admin.login_successful'));

                return redirect()->intended(config('admin.route.prefix'));
            }
            else{
                // TODO: error
                return 'error';
            }
        }
        catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

            // Failed to get the access token or user details.
            exit($e->getMessage());
        }
    }

    protected function getSocialiteProvider(string $providerName){
        $config = (new AdminOauthConfig)->getConfig();
        // get login user
        $socialiteProvider = Socialite::with($providerName)->stateless();
        $scopes = $config['login_providers'][$providerName]['scopes'];
        if($scopes){
            $socialiteProvider = $socialiteProvider->scopes($scopes);
        }
        return $socialiteProvider;
    }

    protected function getAdminOuthRoleSetting($mail){
        // get AdminOauthRoleSetting using mail
        $roleSetting = AdminOauthRoleSetting::where('mail', $mail)->first();
        if($roleSetting){
            return $roleSetting;
        }
        // get AdminOauthRoleSetting using domain
        $roleSetting = AdminOauthRoleSetting::where('domain', explode("@", $mail)[1])->first();
        if($roleSetting){
            return $roleSetting;
        }
        // get AdminOauthRoleSetting all null record
        $roleSetting = AdminOauthRoleSetting::whereNull('mail')->whereNull('domain')->first();
        if($roleSetting){
            return $roleSetting;
        }

        // all null, return null.
        return null;
    }
    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        return Administrator::form(function (Form $form) {
            $form->display('username', trans('admin.username'));
            $form->text('name', trans('admin.name'))->rules('required');
            $form->image('avatar', trans('admin.avatar'));
            $form->password('password', trans('admin.password'))->rules('confirmed|required');
            $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
                ->default(function ($form) {
                    return $form->model()->password;
                });

            $form->setAction(admin_base_path('auth/setting'));

            $form->ignore(['password_confirmation']);

            $form->saving(function (Form $form) {
                if ($form->password && $form->model()->password != $form->password) {
                    $form->password = bcrypt($form->password);
                }
            });

            $form->saved(function () {
                admin_toastr(trans('admin.update_succeeded'));

                return redirect(admin_base_path('auth/setting'));
            });
        });
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

}
