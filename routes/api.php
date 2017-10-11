<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('signUp', 'UserController@store');
Route::post('signIn', 'UserController@signIn');
Route::get('getUser', 'UserController@get');
Route::get('showUser/{id}', 'UserController@show');
Route::get('getUsers', 'UserController@index');
Route::post('editUser', 'UserController@update');
Route::post('reviewUser', 'UserController@review');

Route::get('getSkills', 'SkillsController@index');
Route::post('addSkill', 'SkillsController@store');

Route::get('getUserSkills', 'UserSkillsController@index');
Route::get('getUserSkills/{id}', 'UserSkillsController@show');
Route::post('addUserSkill', 'UserSkillsController@store');
Route::post('removeUserSkill', 'UserSkillsController@delete');

Route::get('getJobs', 'JobsController@index');
Route::get('searchJobs/{search_term}', 'JobsController@search');
Route::get('showJob/{id}', 'JobsController@show');
Route::post('postJob', 'JobsController@store');
Route::post('editJob', 'JobsController@update');
Route::post('removeJob', 'JobsController@delete');

Route::get('getApplications/{id}', 'ApplicationsController@index');
Route::post('submitApplication', 'ApplicationsController@store');
Route::post('reviewApplication', 'ApplicationsController@updateEmployer');
Route::post('acceptOffer', 'ApplicationsController@updateEmployee');

Route::post('addLinkTo{target}', 'LinksController@store');
Route::get('getUserLinks/{id}', 'LinksController@showUser');
Route::get('getJobLinks/{id}', 'LinksController@showJob');
Route::post('editLink', 'LinksController@update');
Route::post('removeLink', 'LinksController@delete');

Route::post('makeAdmin', 'AdminController@store');
Route::post('removeAdmin', 'AdminController@delete');
Route::get('getAdmins', 'AdminController@get');

Route::any('{path?}', 'MainController@index')->where("path", ".+");
