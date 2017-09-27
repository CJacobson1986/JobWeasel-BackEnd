<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::create('applications', function (Blueprint $table) {
           $table->increments('id');

           # backend
           $table->int('user_id');
           $table->int('job_id');
           $table->bool('applicant_reviewed'); // default: false
           $table->bool('employer_approves'); // default: false
           $table->bool('employee_accepts'); // default: false

           $table->timestamps();
         })
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         Schema::dropIfExists('applications');
     }
}
