<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Auth;
use JWTAuth;
use App\Skill;
use App\UserSkill;
use App\User;

class UserSkillsController extends Controller
{
  public function __construct() {
    $this->middleware('jwt.auth', ['only' => ['store']]);
  }

  public function index($id) {
    $skills = Skill::all()->where('user_id', $id);

    return Response::json(['skills' => $skills]);
  }

  # token, skill_id -> user_skill
  public function store(Request $request) {
    $id = Auth::id();
    $user = User::find($id);
    if(empty($user)) {
      return Response::json(['error' => 'User does not exist', 'id' => $id]);
    }

    $rules = [
      'skill_id' => 'required',
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $userSkill = new UserSkill;
    $userSkill->skill_id = $request->input('skill_id');
    $userSkill->user_id = $id;
    $userSkill->save();

    return Response::json([
      'success' => 'UserSkill added',
      'user_skill' => $userSkill
    ]);
  }
}
