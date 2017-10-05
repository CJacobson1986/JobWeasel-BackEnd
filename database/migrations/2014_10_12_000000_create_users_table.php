<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');

            # sign up form data
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            # profile form data
            $table->string('location')->default('');
            $table->integer('phone')->default(0);
            $table->longText('bio'); // default: ""
            $table->longText('photo'); // default: ""

            # backend
            $table->integer('role_id'); // 1: job_poster, 2: job_seeker
            $table->boolean('availability')->default(1);
            $table->boolean('reviewed')->default(0);
            $table->boolean('approved')->default(0);

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
