<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminOauthAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_oauth_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('admin_user_id')->unsigned();
            $table->string('provider_name');
            $table->string('provider_id');
            $table->string('mail')->nullable();
            $table->timestamps();

            $table->index('admin_user_id');
            $table->index('provider_name');
            $table->index('provider_id');
        });

        Schema::create('admin_oauth_role_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('domain')->nullable();
            $table->string('mail')->nullable();
            $table->integer('admin_role_id')->unsigned();
            $table->timestamps();

            $table->index('admin_role_id');
        });


        // update --------------------------------------------------
        //Schema::table('admin_users', function (Blueprint $table) {
        //    $table->string('username')->nullable()->change();
        //    $table->string('password')->nullable()->change();
        //});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_oauth_role_settings');
        Schema::dropIfExists('admin_oauth_accounts');
    }
}
