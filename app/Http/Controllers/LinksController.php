<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Auth;
use JWTAuth;
use App\Link;
use App\User;
use App\Job;

class LinksController extends Controller
{
  public function __construct() {
    $this->middleware('jwt.auth', ['only' => ['store', 'update']]);
  }

  public function index() {
    $links = Link::all();

    return Response::json(['link' => $links]);
  }

  # user_id -> links
  public function showUser($id) {
    $links = Link::where('user_id', '=', $id)->
              where('job_id', '=', 0)->get();

    return Response::json(['links' => $links]);
  }

 # job_id -> links
  public function showJob($id) {
    $links = Link::where('job_id', '=', $id)->get();

    return Response::json(['links' => $links]);
  }

  # "api/addLinkToJob": text, url, job_id, token -> link
  # "api/addLinkToUser": text, url, token -> link
  public function store(Request $request, $target) {
    $user_id = Auth::id();
    $user = User::find($user_id);
    if(empty($user)) {
      return Response::json(['error' => 'User does not exist', 'id' => $user_id]);
    }

    $rules = [
      'text' => 'required',
      'url' => 'required',
    ];
    if($target == 'Job') {
      $rules['job_id'] = 'required';
    }

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $link = new Link;
    $link->text = $request->input('text');
    $link->url = $request->input('url');
    $link->user_id = $user_id;

    if($target == 'Job') {
      $job_id = $request->input('job_id');
      $job = Job::find($job_id);
      if(empty($job)) {
        return Response::json(['error' => 'No job found with this id', 'id' => $job_id]);
      }

      if($job->user_id != $user_id) {
        return Response::json(['error' => 'You are not the poster of this job']);
      }
      $link->job_id = $job_id;
    }

    $link->save();

    return Response::json([
      'success' => 'Link successfully added',
      'link' => $link
    ]);
  }

  public function update(Request $request) {
    $user_id = Auth::id();
    $user = User::find($user_id);
    if(empty($user)) {
      return Response::json(['error' => 'User does not exist', 'id' => $user_id]);
    }

    $rules = [
      'text' => 'required',
      'url' => 'required',
      'link_id' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $link_id = $request->input('link_id');
    $link = Link::find($link_id);

    if($link->user_id != $user_id) {
      return Response::json(['error' => 'You are not the poser of this link']);
    }

    $link->text = $request->input('text');
    $link->url = $request->input('url');

    $link->save();

    return Response::json([
      'success' => 'Link updated successfully',
      'link' => $link
    ]);
  }

  # token, link_id -> null
  public function delete(Request $request) {
    $user_id = Auth::id();

    $rules = ['link_id' => 'required'];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields']);
    }

    $id = $request->input('userSkill_id');
    $link = Link::find($id);

    if(empty($link)) {
      return Response::json(['error' => 'No link exists with that id', 'id' => $id]);
    }

    $admin = !empty(Admin::where('user_id', '=', $user_id)->first());
    $authorized = ($user_id == $link->user_id) || $admin;

    if(!$authorized) {
      return Response::json([
        'error' => 'You are not the poster of this link',
      ]);
    }

    $link->delete();

    return Response::json(['success' => 'Link deleted successfully']);
  }
}
