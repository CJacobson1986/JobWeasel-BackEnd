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

Route::get('getSkills', 'SkillsController@index');
Route::post('addSkill', 'SkillsController@store');

Route::get('getUserSkills/{id}', 'UserSkillsController@index');
Route::post('addUserSkill', 'UserSkillsController@store');

Route::any('{path?}', 'MainController@index')->where("path", ".+");
