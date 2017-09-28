<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Auth;
use JWTAuth;
use App\Skill;
use App\User;

class SkillsController extends Controller
{
  public function __construct() {
    $this->middleware('jwt.auth', ['only' => ['store']]);
  }

  public function index() {
    $skills = Skill::all();

    return Response::json(['skills' => $skills]);
  }

  # token, name -> skill
  public function store(Request $request) {
    $id = Auth::id();
    $user = User::find($id);
    if(empty($user)) {
      return Response::json(['error' => 'User does not exist', 'id' => $id]);
    }

    $rules = [
      'name' => 'required',
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $skill = new Skill;
    $skill->name = $request->input('name');
    $skill->save();

    return Response::json(['success' => 'Skill added', 'skill' => $skill]);
  }
}
