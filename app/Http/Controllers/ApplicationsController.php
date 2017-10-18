<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Auth;
use JWTAuth;
use App\User;
use App\Job;
use App\Application;

class ApplicationsController extends Controller
{
  public function __construct() {
    $this->middleware('jwt.auth', ['only' => [
      'store',
      'updateEmployer',
      'updateEmployee'
      ]]);
  }

  # job_id -> applications
  public function index($id) {
    $applications = Application::where('applications.job_id', '=', $id)
      ->join('users', 'applications.user_id', '=', 'users.id')
      ->orderBy('applications.id', 'desc')
      ->select(
        'applications.id', 'applications.user_id',
        'applications.applicant_reviewed',
        'applications.employer_approves',
        'applications.employee_accepts',
        'users.name')
      ->get();

    return Response::json([
      'applications' => $applications->toArray()
    ]);
  }

  # user_id -> applications
  public function getUserApps($id) {
    $applications = Application::where('applications.user_id', '=', $id)
      ->join('jobs', 'applications.job_id', '=', 'jobs.id')
      ->orderBy('applications.id', 'desc')
      ->select(
        'applications.id', 'applications.job_id',
        'applications.applicant_reviewed',
        'applications.employer_approves',
        'applications.employee_accepts',
        'jobs.name')
      ->get();

    return Response::json([
      'applications' => $applications->toArray()
    ]);
  }

  # token, job_id -> application
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

    $taken = Application::where('user_id', '=', $user_id)
              ->where('job_id', '=', $job_id)
              ->first();
    if(!empty($taken)) {
      return Response::json(['error' => 'You have already applied for this job']);
    }

    $job = Job::find($job_id);
    $user = User::find($user_id);
    if(empty($job)) {
      return Response::json([
        'error' => 'Job posting not found',
        'job_id' => $job_id
      ]);
    }

    $application = new Application;
    $application->user_id = $user_id;
    $application->job_id = $job_id;
    $application->applicant_reviewed = 0;
    $application->employee_accepts = 0;
    $application->employer_approves = 0;
    $application->save();

    return Response::json([
      'success' => 'Application successfully submitted.',
      'application' => $application
    ]);
  }

  # token, application_id, employer_approves -> application
  public function updateEmployer(Request $request) {
    $rules = [
      'application_id' => 'required',
      'employer_approves' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $user_id = Auth::id();
    $user = User::find($user_id);

    $app_id = $request->input('application_id');
    $application = Application::find($app_id);
    if(empty($application)) {
      return Response::json([
        'error' => 'No application found with this id',
        'id' => $app_id
      ]);
    }
    $job = Job::find($application->job_id);
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
    $rules = [
      'application_id' => 'required',
      'employee_accepts' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $user_id = Auth::id();
    $user = User::find($user_id);

    $app_id = $request->input('application_id');
    $application = Application::find($app_id);
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
      return Response::json([
        'error' => 'The job poster has not yet reviewed/accepted this application',
        'application' => $application
      ]);
    }

    $application->employee_accepts = $request->input('employee_accepts');
    $application->save();

    return Response::json([
      'success' => 'You have submitted your response to his job offer.',
      'application' => $application
    ]);
  }

}
