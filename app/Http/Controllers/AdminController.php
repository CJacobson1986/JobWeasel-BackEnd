<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Hash;
use Auth;
use JWTAuth;
use App\Admin;
use App\User;


class AdminController extends Controller
{
  public function __construct() {
    $this->middleware('jwt.auth', ['only' => [
      'store',
      'delete'
      ]]);
  }

  public function store(Request $request) {
    $rules = [
      'user_id' => 'required',
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $user_id = Auth::id();
    if($user_id != 1) {
      return Response::json(['error' => 'You are not authroized to give admin status']);
    }
    $user = User::find($user_id);

    $taken = Admin::where('user_id', '=', $request->input('user_id'))->first();
    if(!empty($taken)) {
      return Response::json([
        'error' => 'This user is already an admin',
        'user' => $taken
      ]);
    }

    $admin = new Admin;
    $admin->user_id = $request->input('user_id');

    $admin_user = User::find($admin->user_id);
    if(empty($admin_user)) {
      return Response::json(['error' => 'No user exists with this id', 'id' => $admin_user->user_id]);
    }
    $admin->save();

    return Response::json([
      'success' => 'Admin added successfully',
      'admin' => $admin,
      'user' => $admin_user
    ]);
  }

  public function get() {
    $admins = Admin::all();

    $users = [];
    foreach ($admins as $admin) {
      $user = User::find($admin->user_id);
      array_push($users, $user);
    }

    return Response::json(["admins" => $users]);
  }

  public function delete(Request $request) {
    $rules = [
      'admin_id' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $user_id = Auth::id();
    if($user_id != 1) {
      return Response::json(['error' => 'You are not authroized to revoke admin status']);
    }

    $id = $request->input('admin_id');
    $admin = Admin::find($id);

    if(empty($admin)) {
      return Response::json(['error' => 'Admin does not exist']);
    }

    $admin_user = User::find($admin->user_id);

    $admin->delete();
    return Response::json([
      'success' => 'Admin deleted successfully',
      'user' => $admin_user
    ]);
  }
}
