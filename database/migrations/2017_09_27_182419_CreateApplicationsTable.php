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
           $table->integer('user_id');
           $table->integer('job_id');
           $table->boolean('applicant_reviewed')->nullable()->default(0);
           $table->boolean('employer_approves')->nullable()->default(0);
           $table->boolean('employee_accepts')->nullable()->default(0);

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
         Schema::dropIfExists('applications');
     }
}
