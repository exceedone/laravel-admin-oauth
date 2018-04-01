<?php

namespace Exceedone\LaravelAdminOauth\Console;

use Exceedone\LaravelAdminOauth\Database\AdminOauthRoleSetting;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Auth\Database\Menu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'adminoauth:install {adminmail?} {domain?} {domain_slug?} {allow_all_users?} {allow_all_users_slug?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the admin-oauth package';

    /**
     * Install directory.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->initDatabase();

        $this->initData();
    }

    /**
     * Create tables and seed it.
     *
     * @return void
     */
    public function initDatabase()
    {
        $this->call('migrate');

        //if (Administrator::count() == 0) {
        //    $this->call('db:seed', ['--class' => \Encore\Admin\Auth\Database\AdminTablesSeeder::class]);
        //}
    }

    private function initData(){

        DB::beginTransaction();
        try
        {
            $slugs = Role::all('slug')->pluck('slug')->toArray();

            // if already executed setting, confirm truncate.
            if(AdminOauthRoleSetting::count() > 0){
                if($this->confirm(trans('adminoauth.console.ask_reset_admin_role'))){
                    AdminOauthRoleSetting::truncate();
                }
            }

            $adminmail = $this->argument('adminmail');
            // if empty $adminmail, insert admin_oauth_role_settings
            if (empty($adminmail)) {
                $adminmail = $this->ask(trans('adminoauth.console.ask_administrator_mail'), false);
            }

            $domain = $this->argument('domain');
            $domain_slug = $this->argument('domain_slug');
            // if empty $domain, insert admin_oauth_role_settings
            if (empty($domain)) {
                $domain = $this->ask(trans('adminoauth.console.ask_domain'), false);
            }
            if(!empty($domain) && empty($domain_slug)){
                $domain_slug = $this->choice(trans('adminoauth.console.ask_role_domain'), $slugs);
            }

            $allow_all_users = $this->argument('allow_all_users');
            if(empty($allow_all_users)){
                $allow_all_users = $this->confirm(trans('adminoauth.console.ask_all_users_login'));
            }

            $allow_all_users_slug = $this->argument('allow_all_users_slug');
            if($allow_all_users){
                if(empty($allow_all_users_slug)){
                    $allow_all_users_slug = $this->choice(trans('adminoauth.console.ask_role_anonymous_user'), $slugs);
                }
            }

            // get administrator role
            $role = Role::where('slug', 'administrator')->first();
            // admin_oauth_role_settings
            $adminmails = explode(',', $adminmail);
            foreach ($adminmails as $adminmail)
            {
                AdminOauthRoleSetting::insert([
                    'mail' => $adminmail
                    , 'admin_role_id' => $role->id
                    , 'created_at' => Carbon::now()
                    , 'updated_at' => Carbon::now()
                ]);
            }

            // get domain's role
            if(!empty($domain_slug)){
                $role = Role::where('slug', $domain_slug)->first();
                // admin_oauth_role_settings
                $domains = explode(',', $domain);
                foreach ($domains as $d)
                {
                    AdminOauthRoleSetting::insert([
                        'domain' => $d
                        , 'admin_role_id' => $role->id
                        , 'created_at' => Carbon::now()
                        , 'updated_at' => Carbon::now()
                    ]);
                }
            }

            //
            if($allow_all_users && !is_null($allow_all_users_slug)){
                $role = Role::where('slug', $allow_all_users_slug)->first();
                AdminOauthRoleSetting::insert([
                      'admin_role_id' => $role->id
                    , 'created_at' => Carbon::now()
                    , 'updated_at' => Carbon::now()
                ]);
            }

            DB::commit();
            $this->info(trans('adminoauth.console.success'));
        }
        catch (Exception $exception)
        {
            DB::rollback();
            $this->info(trans('adminoauth.console.error'));
        }
    }
}
