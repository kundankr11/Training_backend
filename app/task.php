<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class task extends Model
{


   public $table = "task";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'taskDes', 'taskTitle', 'taskStatus', 'dueDate', 'assignee', 'assigner',
    ];

    public function assigned_to(){
        return $this->belongsTo('App\Vmuser', 'assignee');

    }

    public function assigned_by(){
        return $this->belongsTo('App\Vmuser', 'assigner');

    }               



    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}