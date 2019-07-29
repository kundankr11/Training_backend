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
		->where('taskStatus', '!=', 'deleted')->orWhere('taskStatus','!=','completed')
		->where('dueDate','<',Carbon::now())->count();

		// echo $after_duedate ;
		// echo "          ";


		$completed_after_duedate = task::where('assignee','=',$id)
		->where('taskStatus','=',"completed")
		->where('dueDate','<',DB::raw('task.updated_at'))->count();

		// echo $completed_after_duedate;


		$user_info = task::where('assignee','=',$id)
		->where('dueDate','>=',DB::raw('task.updated_at'))	
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

		$total = ($final_data["completed_on_time"] + $final_data["completed_after_deadline"] + $final_data["overdues"] + $final_data["progress"] + $final_data["noActivities"])/100;
		if($total!==0)
		{
		foreach ($final_data as $key => $value) {
			# code...
			$final_data[$key] = $final_data[$key]/$total;
		}
	}




		// $user_info1 = DB::table('task')
		// ->join('vmusers as vm', 'task.assigner', '=', 'vm.id')
		// ->join('vmusers as new', 'new.id', '=', 'task.assignee')
		// ->select('taskStatus')
		// ->where('vm.id', )
		// ->where('task.dueDate','<',Carbon::now())
	 //    ->count();
		// dd($user_info1);


		

		

		
		// $user_info["progress"] = $user_info["in-progress"];
		// unset($user_info["in-progress"]);
		// $user_info["completed"] = $user_info["completed"]/0.25;
		// $user_info["deleted"] = $user_info["deleted"]/0.25;
		// $user_info["assigned"] = $user_info["assigned"]/0.25;
		// $user_info["progress"] = $user_info["progress"]/0.25;
		
		return response()->json([
			'data' => $final_data
		], 201);
	}

}