<?php

namespace App\Http\Controllers;

use App\task;
use App\vmusers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\JWTAuth;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Carbon\Carbon;


class hichartsController extends Controller
{
	// public function retrieveMonths(){
	// 	$month_arr = array();
	// 	$posts_dates = task::orderBy('dueDate', 'ASC' )->pluck('dueDate');
	// 	$timestamp = $posts_dates[0];
	// 	$year = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp)->month->format('F');
	// 	dd($year);
	//     return $posts_dates;
	// }

	public function taskpie(Request $request){

		$pie_data = array();
		$final_data = array(); 
		$curr_user = $request->auth;
		$id = $curr_user->id;
		
		$after_duedate = task::where('assignee', '=', $id)
		->where('taskStatus', '!=', 'deleted')
		->where('taskStatus', '!=', 'completed')
		->where('dueDate','<',Carbon::now())->count();

		$completed_after_duedate = task::where('assignee','=',$id)
		->where('taskStatus','=',"completed")
		->where('dueDate','<',DB::raw('task.updated_at'))->count();

		$user_info = task::where('assignee','=',$id)
		->where('dueDate','>=',Carbon::now())	
		->select('taskStatus', DB::raw('count(*) as total'))
		->groupBy('taskStatus')
		->pluck('total','taskStatus')->all();
		
		if($after_duedate !== 0){
			$pie_data["after_duedate"] = $after_duedate;
		}
		else{
			$pie_data["after_duedate"] = 0;
		}
		if($completed_after_duedate !== 0){
			$pie_data["completed_after_duedate"] = $completed_after_duedate;
		}
		else{
			$pie_data["completed_after_duedate"] = 0;
		}

		if(array_key_exists("assigned", $user_info)){
			$pie_data["noActivities"] = $user_info["assigned"];
		}
		else{
			$pie_data["noActivities"] = 0;
		}

		if(array_key_exists("in-progress", $user_info)){
			$pie_data["inProgress"] = $user_info["in-progress"];
		}
		else{
			$pie_data["inProgress"] = 0;
		}

		if(array_key_exists("completed", $user_info)){
			$pie_data["completed"] = $user_info["completed"];
		}
		else{
			$pie_data["completed"] = 0;
		}

		if(array_key_exists("deleted", $user_info)){
			$pie_data["deleted"] = $user_info["deleted"];
		}
		else{
			$pie_data["deleted"] = 0;
		}
		

		$final_data["completed_on_time"] = $pie_data["completed"];
		$final_data["completed_after_deadline"] = $pie_data["completed_after_duedate"];
		$final_data["overdues"] = $pie_data["after_duedate"];
		$final_data["progress"] = $pie_data["inProgress"];
		$final_data["noActivities"] = $pie_data["noActivities"];

		
		return response()->json([
			'data' => $final_data
		], 201);
	}

}