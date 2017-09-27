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
            $table->string('location'); // default: ""
            $table->int('phone'); // default: 0
            $table->longText('bio'); // default: ""

            # backend
            $table->integer('roleID'); // 0: admin, 1: job_poster, 2: job_seeker
            $table->bool('availability'); // default: true
            $table->bool('reviewed'); // default: false
            $table->bool('approved'); // default: false

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
