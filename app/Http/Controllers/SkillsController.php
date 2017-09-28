<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;

use App\Skill;

class SkillsController extends Controller
{
    public function store(Request $request) {
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
