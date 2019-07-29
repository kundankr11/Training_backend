<?php

namespace App\Http\Controllers;

use App\task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\JWTAuth;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class taskController extends Controller
{
	public function newTask(Request $request)
	{
		$rules = [
			'taskTitle' => 'string|required|min:5',
			'taskDes' => 'string|required|min:10',
			'dueDate' => 'date|required|after:now',
		];
		$this->validate($request, $rules);
		$curr_user = $request->auth;

		$new_task = new task;
		$new_task->taskTitle = $request->taskTitle;
		$new_task->taskDes = $request->taskDes;
		$new_task->assigner = $curr_user->id;
		$new_task->dueDate = $request->dueDate;

		if($curr_user->role === 1){
			$new_task->assignee = $request->assignee;
		}
		else{
			$new_task->assignee = $curr_user->id;
		}

		$new_task->save();
		return response()->json([
			'data' => 'Task created successfully'
		], 201);

	}

	public function creatorUpdate(Request $request)
	{
		$rules = [
			'taskTitle' => 'string|min:5|max:40|nullable',
			'taskDes' => 'string|min:10|max:200|nullable',
			'dueDate' => 'date|after:today|nullable',
			'taskID' => 'integer|required',
		];

		$this->validate($request, $rules);
		$taskTitle = $request->input('taskTitle');
		$taskDes = $request->input('taskDes');
		$dueDate = $request->input('dueDate');
		$taskID = $request->input('taskID');
		$update_task= task::find($taskID);
		$curr_user = $request->auth;

		if($update_task === null)
		{
			return response()->json([
				'error' => 'No such Task exist'
			], 401);
		}
		if($update_task->assigner !== $curr_user->id)
		{
			return response()->json([
				'error' => 'You can only update tasks you have created'
			], 401);
		}
		if($taskTitle === null && $dueDate === null && $taskDes === null)
		{
			return response()->json([
				'error' => 'At least 1 non empty field is required for updation'
			], 401);
		}

		if($update_task!==null && $update_task->taskStatus !== "deleted" )
		{
			if($taskTitle !== null) $update_task->taskTitle = $taskTitle;					
			if($taskDes !== null) $update_task->taskDes = $taskDes;
			if($dueDate !== null) $update_task->dueDate = $dueDate;

			$update_task->save();
			return response()->json([
				'data' => 'Task updated successfully'
			], 201);
		}
		else{
			return response()->json([
				'error' => 'Task Already Deleted'
			], 401);
		}
	}


	public function statusUpdate(Request $request)
	{
		$rules = [
			'taskStatus' => 'required|string|min:5|max:40',
			'taskID' => 'integer|required',
		];
		$this->validate($request, $rules);
		$taskStatus = $request->input('taskStatus');
		$taskID = $request->input('taskID');
		$update_task= task::find($taskID);
		$curr_user = $request->auth;
		if($update_task === null){		
			return response()->json([
				'error' => 'No such Task exist'
			], 401);
		}
		if($update_task->assignee !== $curr_user->id){
			return response()->json([
				'error' => 'You can only update status of tasks you have been assigned to'
			], 401);
		}
		if($taskStatus === null || $taskStatus === $update_task->taskStatus){
			return response()->json([
				'error' => 'Non empty field required'
			], 401);
		}
		if( $update_task->taskStatus !== "deleted" ){
			if($taskStatus === "in-progress" || $taskStatus === "completed"){			
				$update_task->update(['taskStatus'=> $taskStatus]);
				return response()->json([
					'data' => 'Task updated successfully'
				], 201);
			}
			return response()->json([
				'error' => 'Choose correct task Status'
			], 422);
		}
	}

	public function delete(Request $request)
	{
		$rules = [
			'taskID' => 'integer|required',
		];
		$this->validate($request, $rules);
		$curr_user = $request->auth;
		$taskID = $request->input('taskID');
		$update_task= task::find($taskID);
		if($update_task === null){		
			return response()->json([
				'error' => 'No such Task exist'
			], 401);
		}
		if($update_task->assignee !== $curr_user->id){
			return response()->json([
				'error' => 'You can only delete tasks you have been assigned to'
			], 401);
		}

		if($update_task->taskStatus !== "deleted" ){
			$update_task->update(['taskStatus'=> "deleted"]);
			return response()->json([
				'data' => 'Task deleted successfully'
			], 201);
		}
	}

	public function tasklisting(Request $request)
	{
		$rules = [
			'dueDate'=> 'date|nullable',
			'status' => 'string|max:20|nullable',
			'assignee' => 'string|nullable',
			'assigner' => 'string|nullable',
			'taskDes' => 'string|max:10|nullable',
			'taskTitle' => 'string|max:10|nullable',
			'beforedate' => 'date|nullable',
			'taskID' => 'integer|nullable',
		];
		$curr_user = $request->auth;


		$this->validate($request, $rules);
		$taskTitle = $request->input('taskTitle');
		$taskDes = $request->input('taskDes');
		$assignee = $request->input('assignee');
		$dueDate = $request->input('dueDate');
		$beforedate = $request->input('beforedate');
		$assigner = $request->input('assigner');
		$status = $request->input('status');

		$taskID = $request->input('taskID');

		$result = task::where(function($query) use ($taskTitle, $taskDes, $assignee, $assigner, $dueDate, $status, $beforedate, $curr_user, $taskID){
		// ->join('vmusers as vm', 'task.assigner', '=', 'vm.id')
		// ->join('vmusers as new', 'new.id', '=', 'task.assignee')
		// ->select('task.id', 'task.taskTitle', 'task.taskStatus','task.taskDes','new.name as Assignee', 'vm.name as Assigner', 'task.dueDate')

			if($curr_user->role !== 1) $query->where('task.assignee', '=', $curr_user->id);
			if(0) $query->where('task.taskStatus','!=',"deleted");
			if(!is_null($taskTitle)) $query->where('task.taskTitle', 'LIKE','%'.$taskTitle.'%');
			if(!is_null($taskDes)) $query->where('task.taskDes', 'LIKE','%'.$taskDes.'%');
			if(!is_null($status)) $query->where('task.taskStatus','LIKE','%'.$status.'%');
			$query->where('task.taskStatus','!=',"deleted");
			if(!is_null($dueDate)) $query->where('task.dueDate','>',$dueDate);
			if(!is_null($beforedate)) $query->where('task.dueDate','<',$beforedate);
			if(!is_null($taskID)){ $query->where('task.id','=',$taskID); };
		})
		->whereHas('assigned_to', function($query) use ($assignee, $assigner){
			if(!is_null($assignee)) $query->where('name', 'LIKE', '%'.$assignee.'%');

		})
		->whereHas('assigned_by', function($query) use ($assignee, $assigner){

			if(!is_null($assigner)) $query->where('name', 'LIKE', '%'.$assigner.'%');
		})
		->with('assigned_to:id,name' , 'assigned_by:id,name')

		->paginate(5);
		return response()->json([
			'table' => $result
		], 201);
	   	// $result = task::where(function($query) use ($taskTitle, $taskDes, $assigner, $dueDate, $status, $curr_user, $taskID){
	    //                    if(!is_null($taskTitle)) {$query->where('task.taskTitle', 'LIKE','%'.$taskTitle.'%');}
	    //                    $query->where('assigner', '=', $curr_user->id)->orWhere('assignee', '=', $curr_user->id);
		//             	if(1) $query->where('task.taskStatus','!==',"deleted");
		//             	if(!is_null($taskDes)) {$query->where('task.taskDes', 'LIKE','%'.$taskDes.'%');}
		// 	            if(!is_null($status)) $query->where('task.taskStatus','LIKE','%'.$status.'%');
		// 	            if(!is_null($dueDate)) $query->where('task.dueDate','>',$dueDate);
		// 	            if(!is_null($taskID)) $query->where('task.id','=',$taskID);		           
		//                 })

		//                 // ->whereHas('assigned_by', function($query) use ($assigner){
		//                 // if(!is_null($assigner)) $query->where('name', 'LIKE', '%'.$assigner.'%');
		//                 // })
		//                 // ->with('assigned_to')
		//                 ->get();

		//                 return $result;

	}  

	public function updatelisting(Request $request)
	{
		$rules = [
			'dueDate'=> 'date|nullable',
			'status' => 'string|max:20|nullable',
			'assignee' => 'string|nullable',
			'taskDes' => 'string|max:10|nullable',
			'taskTitle' => 'string|max:10|nullable',
			'beforedate' => 'date|nullable',
			'taskID' => 'integer|nullable',
		];
		$curr_user = $request->auth;

		$this->validate($request, $rules);
		$taskTitle = $request->input('taskTitle');
		$taskDes = $request->input('taskDes');
		$assignee = $request->input('assignee');
		$assigner = $request->input('assigner');
		$dueDate = $request->input('dueDate');
		$beforedate = $request->input('beforedate');
		$status = $request->input('status');
		$taskID = $request->input('taskID');


        $result = task::where(function($query) use ($taskTitle, $taskDes, $assignee, $assigner, $dueDate, $status, $beforedate, $curr_user, $taskID){
		// ->join('vmusers as vm', 'task.assigner', '=', 'vm.id')
		// ->join('vmusers as new', 'new.id', '=', 'task.assignee')
		// ->select('task.id', 'task.taskTitle', 'task.taskStatus','task.taskDes','new.name as Assignee', 'vm.name as Assigner', 'task.dueDate')

			$query->where('task.assigner', '=', $curr_user->id);
			if(!is_null($taskTitle)) $query->where('task.taskTitle', 'LIKE','%'.$taskTitle.'%');
			if(!is_null($taskDes)) $query->where('task.taskDes', 'LIKE','%'.$taskDes.'%');
			if(!is_null($status)) $query->where('task.taskStatus','LIKE','%'.$status.'%');
			$query->where('task.taskStatus','!=',"deleted");
			if(!is_null($dueDate)) $query->where('task.dueDate','>',$dueDate);
			if(!is_null($beforedate)) $query->where('task.dueDate','<',$beforedate);
			if(!is_null($taskID)){ $query->where('task.id','=',$taskID); };
		})
		->whereHas('assigned_to', function($query) use ($assignee, $assigner){
			if(!is_null($assignee)) $query->where('name', 'LIKE', '%'.$assignee.'%');

		})
		->whereHas('assigned_by', function($query) use ($assignee, $assigner){

			if(!is_null($assigner)) $query->where('name', 'LIKE', '%'.$assigner.'%');
		})
		->with('assigned_to:id,name' , 'assigned_by:id,name')

		->paginate(5);
		return response()->json([
			'table' => $result
		], 201);
        

		// $result = DB::table('task')
		// ->join('vmusers as vm', 'task.assigner', '=', 'vm.id')
		// ->join('vmusers as new', 'new.id', '=', 'task.assignee')
		// ->select('task.id', 'task.taskTitle', 'task.taskStatus','task.taskDes','new.name as Assignee', 'vm.name as Assigner', 'task.dueDate')
		// ->where(function($query) use ($taskTitle, $taskDes, $assignee, $dueDate, $status, $beforedate, $curr_user, $taskID){
		// 	$query->where('task.assigner', '=', $curr_user->id);
		// 	if(!is_null($assignee)) $query->where('new.name', 'LIKE','%'.$assignee.'%');
		// 	if(!is_null($taskTitle)) $query->where('task.taskTitle', 'LIKE','%'.$taskTitle.'%');
		// 	if(!is_null($taskDes)) $query->where('task.taskDes', 'LIKE','%'.$taskDes.'%');
		// 	if(!is_null($status)) $query->where('task.taskStatus','LIKE','%'.$status.'%');
		// 	$query->where('task.taskStatus','!=',"deleted");
		// 	if(!is_null($dueDate)) $query->where('task.dueDate','>',$dueDate);
		// 	if(!is_null($beforedate)) $query->where('task.dueDate','<',$beforedate);
		// 	if(!is_null($taskID)) $query->where('task.id','=',$taskID);
		// })->paginate(5);
		// return response()->json([
		// 	'table' => $result
		// ], 201);

	}

	public function statuslisting(Request $request)
	{
		$rules = [
			'dueDate'=> 'date|nullable',
			'status' => 'string|max:20|nullable',
			'assigner' => 'string|nullable',
			'taskDes' => 'string|max:10|nullable',
			'taskTitle' => 'string|max:10|nullable',
			'taskID' => 'integer|nullable',
		];
		$curr_user = $request->auth;

		$this->validate($request, $rules);
		$taskTitle = $request->input('taskTitle');
		$taskDes = $request->input('taskDes');
		$assigner = $request->input('assigner');
		$dueDate = $request->input('dueDate');
		$status = $request->input('status');
		$taskID = $request->input('taskID');
		$pie = $request->input('pie');
		$assignee = $curr_user->name;

        $result = task::where(function($query) use ($taskTitle, $taskDes, $assignee, $assigner, $dueDate, $status, $curr_user, $taskID, $pie){
		$query->where('task.assignee', '=', $curr_user->id);
			if(!is_null($taskTitle)) $query->where('task.taskTitle', 'LIKE','%'.$taskTitle.'%');
			if(!is_null($taskDes)) $query->where('task.taskDes', 'LIKE','%'.$taskDes.'%');
			if(!is_null($status)) $query->where('task.taskStatus','LIKE','%'.$status.'%');
			$query->where('task.taskStatus','!=',"deleted");
			if(!is_null($dueDate)) $query->where('task.dueDate','>',$dueDate);
			if(!is_null($taskID)){ $query->where('task.id','=',$taskID); };
			if($pie) $query->where('taskStatus', '=',"assigned" )->orWhere('taskStatus', '=',"in-progress" );
		})
		->whereHas('assigned_to', function($query) use ($assignee, $assigner){
			if(!is_null($assignee)) $query->where('name', 'LIKE', '%'.$assignee.'%');

		})
		->whereHas('assigned_by', function($query) use ($assignee, $assigner){

			if(!is_null($assigner)) $query->where('name', 'LIKE', '%'.$assigner.'%');
		})
		->with('assigned_to:id,name' , 'assigned_by:id,name')
        ->orderBy('dueDate')
		->paginate(5);
		return response()->json([
			'table' => $result
		], 201);
        

		// $result = DB::table('task')
		// ->join('vmusers as vm', 'task.assigner', '=', 'vm.id')
		// ->join('vmusers as new', 'new.id', '=', 'task.assignee')
		// ->select('task.id', 'task.taskTitle', 'task.taskStatus','task.taskDes','new.name as Assignee', 'vm.name as Assigner', 'task.dueDate')
		// ->where(function($query) use ($taskTitle, $taskDes, $assigner, $dueDate, $status, $curr_user, $taskID, $pie){
		// 	$query->where('task.assignee', '=', $curr_user->id);
		// 	if(!is_null($assigner)) $query->where('new.name', 'LIKE','%'.$assigner.'%');
		// 	if(!is_null($taskTitle)) $query->where('task.taskTitle', 'LIKE','%'.$taskTitle.'%');
		// 	$query->where('task.taskStatus','!=',"deleted");
		// 	if(!is_null($taskDes)) $query->where('task.taskDes', 'LIKE','%'.$taskDes.'%');
		// 	if(!is_null($status)) $query->where('task.taskStatus','LIKE','%'.$status.'%');
		// 	if(!is_null($dueDate)) $query->where('task.dueDate','>',$dueDate);
		// 	if(!is_null($taskID)) $query->where('task.id','=',$taskID);
		// 	if($pie) $query->where('taskStatus', '=',"assigned" )->orWhere('taskStatus', '=',"in-progress" );

		//     })
  //       ->orderBy('dueDate')
		// ->paginate(5);
		// return response()->json([
		// 	'table' => $result
		// ], 201);

	}







}