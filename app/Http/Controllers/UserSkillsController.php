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
use App\Admin;

class UserSkillsController extends Controller
{
  public function __construct() {
    $this->middleware('jwt.auth', ['only' => ['store', 'delete']]);
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
      $skill->userSkill_id = $match->id;
      array_push($skills, $skill);
    }

    return Response::json(['skills' => $skills]);
  }

  # token, skill_name -> user_skill
  public function store(Request $request) {
    $id = Auth::id();
    $user = User::find($id);

    $rules = [
      'skill_name' => 'required',
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $skill_name = $request->input('skill_name');
    $skill = Skill::where('name', '=', $skill_name)->first();

    if(empty($skill)) {
      $skill = new Skill;
      $skill->name = $skill_name;
      $skill->save();
    }

    if(!empty(
      UserSkill::where('user_id', '=', $id)->where('skill_id', '=', $skill->id)->first()
      )) {
        return Response::json([
          'error' => 'User has already added skill',
          'user' => $user,
          'skill' => $skill
        ]);
    }

    $userSkill = new UserSkill;
    $userSkill->skill_id = $skill->id;
    $userSkill->user_id = $id;
    $userSkill->save();

    return Response::json([
      'success' => 'UserSkill added',
      'user_skill' => $userSkill
    ]);
  }

  # token, skill_name -> null
  public function delete(Request $request) {
    $user_id = Auth::id();

    $rules = ['skill_name' => 'required'];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields']);
    }

    $skill_name = $request->input('skill_name');
    $skill = Skill::where('name', '=', $skill_name)->first();

    if(empty($skill)) {
      return Response::json(['error' => 'No skill exists with name :' + $skill_name]);
    }

    $userSkill = UserSkill::where('user_id', '=', $user_id)->where('skill_id', '=', $skill->id)->first();

    if(empty($userSkill)) {
      return Response::json([
        'error' => 'No UserSkill exists for that skill/user_id',
        'skill' => $skill,
        'user_id' => $user_id
      ]);
    }

    $admin = !empty(Admin::where('user_id', '=', $user_id)->first());
    $authorized = ($user_id == $userSkill->user_id) || $admin;

    if(!$authorized) {
      return Response::json([
        'error' => 'You are not the user of this skill',
      ]);
    }

    $userSkill->delete();

    return Response::json(['success' => 'UserSkill removed successfully']);
  }
}
