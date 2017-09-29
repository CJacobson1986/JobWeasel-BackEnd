<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Hash;
use Auth;
use JWTAuth;
use App\User;

class UserController extends Controller
{
  public function __construct() {
    $this->middleware('jwt.auth', ['only' => ['get', 'update']]);
  }

  # token -> user
  public function get() {
    $id = Auth::id();
    $user = User::find($id);
    if(empty($user)) {
      return Response::json(['error' => 'User does not exist', 'id' => $id]);
    }

    return Response::json([
      'user' => $user,
      'success' => 'User is logged in'
    ]);
  }

  # ?page=pageNum -> users
  public function index() {
    $users = User::orderBy('id', 'asc')->paginate(25);

    return Response::json(['users' => $users]);
  }

  # id -> user
  public function show($id) {
    $user = User::find($id);
    if(empty($user)) {
      return Response::json(['error' => 'User does not exist', 'id' => $id]);
    }

    return Response::json(['user' => $user]);
  }

  # name, email, password -> user
  public function store(Request $request) {
    $rules = [
      'name' => 'required',
      'email' => 'required',
      'password' => 'required',
      'role_id' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $email = $request->input('email');
    $check_email = User::where('email', '=', $email)->first();
    if(!empty($check_email)) {
      return Response::json(['error' => 'That email is already taken.']);
    }

    $name =  $request->input('name');
    $role = $request->input('role_id');
    $password = $request->input('password');
    $password = Hash::make($password);

    $user = new User;
    $user->email = $email;
    $user->name = $name;
    $user->password = $password;
    $user->role_id = $role;
    $user->bio = "";
    $user->save();

    return Response::json([
      'success' => 'Thanks for signing up!',
      'user' => $user
    ]);
  }

  # token, bio, phone, location -> user
  public function update(Request $request) {
    $rules = [
      'bio' => 'required',
      'phone' => 'required',
      'location' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $id = Auth::id();
    $user = User::find($id);
    if(empty($user)) {
      return Response::json(['error' => 'User does not exist', 'id' => $id]);
    }

    $bio = $request->input('bio');
    $location = $request->input('location');
    $phone = $request->input('phone');

    $user->bio = $bio;
    $user->location = $location;
    $user->phone = $phone;
    $user->save();

    return Response::json([
      'success' => 'Profile udated successfully!',
      'user' => $user
    ]);
  }

  # email, password -> token
  public function signIn(Request $request) {
    $rules = [
      'email' => 'required',
      'password' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $email = $request->input('email');
    $password = $request->input('password');
    $credentials = compact('email', 'password');

    $token = JWTAuth::attempt($credentials);

    if ($token == false) {
      return Response::json(['error' => 'Wrong Email/Password']);
    }
    else {
      return Response::json([
        'token' => $token,
        'success' => 'Logged in successfully.',
      ]);
    }
  }
}
