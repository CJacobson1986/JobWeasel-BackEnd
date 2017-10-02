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
use App\Job;

class JobsController extends Controller
{
  public function __construct() {
    $this->middleware('jwt.auth', ['only' => ['store', 'update']]);
  }

  # ?page=pageNum -> jobs
  public function index() {
    $jobs = Job::orderBy('id', 'asc')->paginate(25);

    return Response::json(['jobs' => $jobs]);
  }

  # id -> job
  public function show($id) {
    $job = Job::find($id);
    if(empty($job)) {
      return Response::json(['error' => 'Job does not exist', 'id' => $id]);
    }

    return Response::json(['job' => $job]);
  }

  # token, title, description, workers_needed, budget, start_date, time_frame -> job
  public function store(Request $request) {
    $user_id = Auth::id();
    $user = User::find($user_id);
    if(empty($user)) {
      return Response::json(['error' => 'User does not exist', 'id' => $user_id]);
    }
    if ($user->role_id != 1) {
      return Response::json([
        'error' => 'Your account is not authroized to post job listings',
        'user' => $user,
      ]);
    }

    $rules = [
      'name' => 'required',
      'description' => 'required',
      'workers_needed' => 'required',
      'budget' => 'required',
      'start_date' => 'required',
      'time_frame' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $job = new Job;
    $job->name = $request->input('name');
    $job->user_id = $user_id;
    $job->description = $request->input('description');
    $job->budget = $request->input('budget');
    $job->workers_needed = $request->input('workers_needed');
    $job->start_date = date("Y-m-d", strtotime($request->input('start_date')));
    $job->time_frame = $request->input('time_frame');
    $job->save();

    return Response::json([
      'success' => 'Job posted successfully!',
      'job' => $job
    ]);
  }

  # token, title, description, workers_needed, budget, start_date, time_frame -> job
  public function update(Request $request) {
    $user_id = Auth::id();
    $user = User::find($user_id);
    if(empty($user)) {
      return Response::json(['error' => 'User does not exist', 'id' => $id]);
    }

    $rules = [
      'name' => 'required',
      'description' => 'required',
      'workers_needed' => 'required',
      'budget' => 'required',
      'start_date' => 'required',
      'time_frame' => 'required',
      'job_id' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $job_id = $request->input('job_id');
    $job = Job::find($job_id);
    if($job->user_id != $user_id) {
      return Response::json(['error' => 'You are not the poster of this job listing.']);
    }

    $job->name = $request->input('name');
    $job->description = $request->input('description');
    $job->budget = $request->input('budget');
    $job->workers_needed = $request->input('workers_needed');
    $job->start_date = $request->input('start_date');
    $job->time_frame = $request->input('time_frame');
    $job->save();

    return Response::json([
      'success' => 'Job posting udated successfully!',
      'job' => $job
    ]);
  }
}
