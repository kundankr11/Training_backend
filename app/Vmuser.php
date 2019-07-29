<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vmuser extends Model
{


   public $table = "vmusers";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',  'created_by', 'deleted_by', 'updated_by', 'role',
    ];

    public function task_assigned_to()
    {
        return $this->hasMany('App\task','assignee'); 
    }

    public function task_assigned_by()
    {
        return $this->hasMany('App\task','assigner'); 
    }

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];
}