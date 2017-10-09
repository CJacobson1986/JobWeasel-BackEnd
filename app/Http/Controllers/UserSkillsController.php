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

  public function index() {
    $userSkills = UserSkill::all();

    return Response::json(['user_skills' => $userSkills]);
  }

  # user_id -> skills
  public function show($id) {
    $id = (int) $id;
    $matches = UserSkill::where('user_id', $id)->get();
    $skills = [];
    foreach ($matches as $match) {
      $skill = Skill::find($match->skill_id);
      array_push($skills, $skill);
    }

    return Response::json([
      'skills' => $skills,
      'id' => $id
    ]);
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
