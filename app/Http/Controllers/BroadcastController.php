<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Pusher\Pusher;


class BroadcastController extends Controller {

	public function authenticate(Request $request)
	{  
		$app_id = "834772";
		$app_key = "fea611aa8ced2588b8e6";
		$app_secret = "c4aa407b0775500f22d7";
		$app_cluster = "ap2";
		$pusher = new Pusher($app_key, $app_secret, $app_id, array('cluster' => $app_cluster));
		$auth=$pusher->socket_auth($request->input('channel_name'), $request->input('socket_id'));
		// return response()->json([$auth]);
		return $auth;
	}
}
