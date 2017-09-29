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
use App\Application;

class ApplicationsController extends Controller
{
  public function __construct() {
    $this->middleware('jwt.auth', ['only' => [
      'get',
      'updateEmployer',
      'updateEmployee'
      ]]);
  }

  # id -> applications
  public function index($id) {
    $applications = Application::all()->
      where('job_id', '=', $id)->
      where('applicant_reviewed', '=', 0);

    return Response::json([
      'applications' => $applications
    ]);
  }

  # user_id, job_id -> application
  public function store(Request $request) {
    $rules = [
      'job_id' => 'required',
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $job_id = $request->input('job_id');
    $user_id = Auth::id();
    $job = Job::find($job_id);
    $user = User::find($user_id);
    if(empty($job)) {
      return Response::json([
        'error' => 'Job posting not found',
        'job_id' => $job_id
      ]);
    }
    // if($user->role_id != 2) {
    //   return Response::json([
    //     'error' => 'Your account is not authorized to post job applications.',
    //     'user' => $user
    //   ]);
    // }

    $application = new Application;
    $application->user_id = $user_id;
    $application->job_id = $job_id;
    $application->save();

    return Response::json([
      'success' => 'Application successfully submitted.',
      'application' => $application
    ]);
  }

  # token, application_id, employer_approves -> application
  public function updateEmployer(Request $request) {
    $user_id = Auth::id();
    $user = User::find();

    $app_id = $request->input('application_id');
    $application = Application::find();
    if(empty($application)) {
      return Response::json([
        'error' => 'No application found with this id',
        'id' => $app_id
      ]);
    }
    $job = Job::find($applicaion->job_id);
    if($user_id != $job->user_id) {
      return Response::json([
        'error' => 'You are not the poster of this job listing.'
      ]);
    }

    $approval = $request->input('employer_approves');
    $application->applicant_reviewed = 1;
    $application->employer_approves = $approval;
    $application->save();

    return Response::json([
      'success' => 'Job application response saved!',
      'application' => $application
    ]);
  }

  # token, application_id -> application
  public function updateEmployee(Request $request) {
    $user_id = Auth::id();
    $user = User::find();

    $app_id = $request->input('application_id');
    $application = Application::find();
    if(empty($application)) {
      return Response::json([
        'error' => 'No application found with this id',
        'id' => $app_id
      ]);
    }

    if($user_id != $application->user_id) {
      return Response::json([
        'error' => 'You are not the submitter of this application',
        'id' => $app_id
      ]);
    }

    $applicant_reviewed = $application->applicant_reviewed;
    $employer_approves = $application->employer_approves;

    if(!($applicant_reviewed && $employer_approves)) {
      $user_id = Auth::id();
      $user = User::find();

      $app_id = $request->input('application_id');
      $application = Application::find();
      if(empty($application)) {
        return Response::json([
          'error' => 'The job poster has not yet reviewed/accepted this application',
          'application' => $application
        ]);
      }
    }

    $application->employee_accepts = 1;
    $application->save();

    return Response::json([
      'success' => 'You have accepted this job offer. Congratulations!',
      'application' => $application
    ]);
  }

}
