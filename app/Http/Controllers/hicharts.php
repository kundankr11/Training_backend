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


class hicharts extends Controller
{
	public function retrieveMonths(){
		$month_arr = array();
		$posts_dates = task::orderBy('dueDate', 'ASC' )->pluck('dueDate');
		$t = $posts_dates[0];
		$t = date_format('m');
	    

	}
	
}