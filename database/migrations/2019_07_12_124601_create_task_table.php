<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('taskDes');
            $table->string('taskTitle');
            $table->string('taskStatus')->default('assigned');
            $table->integer('assignee');
            $table->dateTime('dueDate');
            $table->integer('assigner');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task');
    }
}







    // $result = task::where('assignee','=',$curr_user->id)
    //                     ->with('vmuser1')
    //                     ->where(function($query) use ($taskTitle, $taskDes, $assigner, $dueDate, $status, $curr_user, $taskID){
    //                     if(!is_null($assigner)) $query->where('new.name', 'LIKE','%'.$assigner.'%');
    //                     if(!is_null($taskTitle)) $query->where('task.taskTitle', 'LIKE','%'.$taskTitle.'%');
    //                     if(1) $query->where('task.taskStatus','!=',"deleted");
    //                     if(!is_null($taskDes)) $query->where('task.taskDes', 'LIKE','%'.$taskDes.'%');
    //                     if(!is_null($status)) $query->where('task.taskStatus','LIKE','%'.$status.'%');
    //                     if(!is_null($dueDate)) $query->where('task.dueDate','>',$dueDate);
    //                     if(!is_null($taskID)) $query->where('task.id','=',$taskID);

    //                     })
                         
    //                      ->get();
                        
    //     // $result = DB::table('task')
    //     // ->join('vmusers as vm', 'task.assigner', '=', 'vm.id')
    //     // ->join('vmusers as new', 'new.id', '=', 'task.assignee')

    //     // ->select('task.id', 'task.taskTitle', 'task.taskStatus','task.taskDes','new.name as Assignee', 'vm.name as Assigner', 'task.dueDate')
    //     // ->where(function($query) use ($taskTitle, $taskDes, $assigner, $dueDate, $status, $curr_user, $taskID){
    //     //  $query->where('task.assignee', '=', $curr_user->id);
    //     //  if(!is_null($assigner)) $query->where('new.name', 'LIKE','%'.$assigner.'%');
    //     //  if(!is_null($taskTitle)) $query->where('task.taskTitle', 'LIKE','%'.$taskTitle.'%');
    //     //  if(1) $query->where('task.taskStatus','!=',"deleted");
    //     //  if(!is_null($taskDes)) $query->where('task.taskDes', 'LIKE','%'.$taskDes.'%');
    //     //  if(!is_null($status)) $query->where('task.taskStatus','LIKE','%'.$status.'%');
    //     //  if(!is_null($dueDate)) $query->where('task.dueDate','>',$dueDate);
    //     //  if(!is_null($taskID)) $query->where('task.id','=',$taskID);
    //     // })->paginate(5);
    //     return response()->json([
    //         'table' => $result
    //     ], 201);

